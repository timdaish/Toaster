const CDP = require('chrome-remote-interface');
const argv = require('minimist')
const fs = require('fs');
const args = process.argv;
console.log(args);

// CLI Args
const viewportWidth =  1440;
const viewportHeight =  900;

// Start the Chrome Debugging Protocol
CDP(async (client) => {
    const {DOM, Emulation, Network, Page, Runtime} = client;
    try {
        await Page.enable();
        await DOM.enable();
        await Network.enable();
 

        // If user agent override was specified, pass to Network domain
        //if (userAgent) {
        //    await Network.setUserAgentOverride({userAgent});
        //}

        // Set up viewport resolution, etc.
        const deviceMetrics = {
            width: viewportWidth,
            height: viewportHeight,
            deviceScaleFactor: 0,
            mobile: false,
            fitWindow: false,
        };
        await Emulation.setDeviceMetricsOverride(deviceMetrics);
        await Emulation.setVisibleSize({width: viewportWidth, height: viewportHeight});

        // Navigate to target page
        await Page.navigate({url: args[2]});


        await Page.loadEventFired();
        const {data} = await Page.captureScreenshot();
        fs.writeFileSync(args[3], Buffer.from(data, 'base64'));
    } catch (err) {
        console.error(err);
    } finally {
        await client.close();
    }
}).on('error', (err) => {
    console.error(err);
});

