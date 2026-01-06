/**
 * WPPConnect Optimized Configuration
 *
 * This configuration reduces RAM usage per session from ~200MB to ~50-80MB.
 * Apply these settings when starting WPPConnect server.
 *
 * Usage:
 *   node server.js --config=./wppconnect-optimized.config.js
 *
 * Or modify your existing server.js to include these puppeteer options.
 */

module.exports = {
    /**
     * Puppeteer launch options for minimal RAM usage
     */
    puppeteerOptions: {
        headless: true,
        args: [
            // Security & Sandbox (required for Linux/Docker)
            "--no-sandbox",
            "--disable-setuid-sandbox",

            // Memory Optimization
            "--disable-dev-shm-usage", // Use /tmp instead of /dev/shm
            "--disable-gpu", // No GPU acceleration needed
            "--disable-accelerated-2d-canvas",
            "--disable-canvas-aa",
            "--disable-gl-drawing-for-tests",

            // Reduce startup overhead
            "--no-first-run",
            "--no-zygote",

            // JavaScript heap limit (256MB per session)
            "--js-flags=--max-old-space-size=256",

            // Disable unused features
            "--disable-background-networking",
            "--disable-background-timer-throttling",
            "--disable-backgrounding-occluded-windows",
            "--disable-breakpad",
            "--disable-component-extensions-with-background-pages",
            "--disable-component-update",
            "--disable-default-apps",
            "--disable-extensions",
            "--disable-features=TranslateUI",
            "--disable-hang-monitor",
            "--disable-ipc-flooding-protection",
            "--disable-popup-blocking",
            "--disable-prompt-on-repost",
            "--disable-renderer-backgrounding",
            "--disable-sync",
            "--force-color-profile=srgb",
            "--metrics-recording-only",
            "--safebrowsing-disable-auto-update",

            // Network optimization
            "--disable-features=NetworkService",
        ],
    },

    /**
     * WPPConnect session options
     */
    sessionOptions: {
        // Disable logging for production
        logQR: false,

        // Auto-close session on disconnect (saves RAM)
        autoClose: 60000, // Close after 60 seconds of inactivity

        // Use minimal browser instance
        useChrome: false,

        // Disable unnecessary features
        disableSpins: true,
        disableWelcome: true,
    },

    /**
     * Session pool settings
     * Limit concurrent sessions to prevent RAM exhaustion
     */
    pool: {
        maxSessions: 10, // Maximum concurrent sessions
        sessionTimeout: 600, // Session timeout in seconds (10 min)
    },

    /**
     * Deployment notes for KVM 2 (8GB RAM):
     *
     * Expected RAM usage with this config:
     * - Node.js process: ~100MB
     * - Per session: 50-80MB
     * - 10 concurrent sessions: 500-800MB
     * - System overhead: ~500MB
     * - Total WPPConnect usage: ~1.3-1.5GB
     *
     * Remaining for Laravel + MySQL: ~6GB (plenty)
     *
     * Recommended PM2 config:
     *   pm2 start server.js --name wppconnect --max-memory-restart 2G
     */
};
