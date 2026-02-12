import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  ListToolsRequestSchema,
  CallToolRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { spawn } from "node:child_process";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Repo root = one level up from /mcp
const ROOT = path.resolve(__dirname, "..");
const SCRIPTS_DIR = path.join(ROOT, "scripts");

// Only allow these scripts (explicit allow-list)
const ALLOWED = {
  "dev-up": path.join(SCRIPTS_DIR, "dev-up.ps1"),
  "wp-install": path.join(SCRIPTS_DIR, "wp-install.ps1"),
  "dev-reset": path.join(SCRIPTS_DIR, "dev-reset.ps1"),
  "test": path.join(SCRIPTS_DIR, "test.ps1"),
  "plugin-activate": path.join(SCRIPTS_DIR, "plugin-activate.ps1"),
  "plugin-deactivate": path.join(SCRIPTS_DIR, "plugin-deactivate.ps1"),
};

function runPowerShellScript(scriptPath) {
  return new Promise((resolve) => {
    const ps = spawn(
      "powershell.exe",
      ["-NoProfile", "-ExecutionPolicy", "Bypass", "-File", scriptPath],
      { cwd: ROOT }
    );

    let stdout = "";
    let stderr = "";

    ps.stdout.on("data", (d) => (stdout += d.toString()));
    ps.stderr.on("data", (d) => (stderr += d.toString()));

    ps.on("close", (code) => {
      resolve({
        code,
        stdout: stdout.trim(),
        stderr: stderr.trim(),
      });
    });
  });
}

const server = new Server(
  { name: "kiro-local-mcp", version: "0.1.0" },
  { capabilities: { tools: {} } }
);

server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: "wp_dev_up",
        description: "Start the local WordPress dev stack (docker compose up -d).",
        inputSchema: { type: "object", properties: {} },
      },
      {
        name: "wp_install",
        description: "Install WordPress (if needed) and activate the wp-forever plugin.",
        inputSchema: { type: "object", properties: {} },
      },
      {
        name: "wp_dev_reset",
        description: "Reset local stack (docker compose down -v).",
        inputSchema: { type: "object", properties: {} },
      },
      {
        name: "wp_test",
        description: "Run fast local checks (PHP lint + plugin active check).",
        inputSchema: { type: "object", properties: {} },
      },
      {
        name: "wp_plugin_activate",
        description: "Activate the WP Forever plugin.",
        inputSchema: { type: "object", properties: {} },
      },
      {
        name: "wp_plugin_deactivate",
        description: "Deactivate the WP Forever plugin.",
        inputSchema: { type: "object", properties: {} },
      },
    ],
  };
});

server.setRequestHandler(CallToolRequestSchema, async (req) => {
  const name = req.params.name;

  let key;
  if (name === "wp_dev_up") key = "dev-up";
  else if (name === "wp_install") key = "wp-install";
  else if (name === "wp_dev_reset") key = "dev-reset";
  else if (name === "wp_test") key = "test";
  else if (name === "wp_plugin_activate") key = "plugin-activate";
  else if (name === "wp_plugin_deactivate") key = "plugin-deactivate";
  else {
    return {
      content: [{ type: "text", text: `Unknown tool: ${name}` }],
      isError: true,
    };
  }

  const scriptPath = ALLOWED[key];
  const result = await runPowerShellScript(scriptPath);

  const text =
    `Exit code: ${result.code}\n\n` +
    (result.stdout ? `STDOUT:\n${result.stdout}\n\n` : "") +
    (result.stderr ? `STDERR:\n${result.stderr}\n` : "");

  return { content: [{ type: "text", text }], isError: result.code !== 0 };
});

const transport = new StdioServerTransport();
await server.connect(transport);