const CDP = require('chrome-remote-interface');
const fs = require('fs');

CDP(async (client) => {
    const {Page} = client;
    try {
        await Page.enable();
        await Page.navigate({url: 'http://www.bbc.co.uk'});
        await Page.loadEventFired();
        const {data} = await Page.captureScreenshot();
        fs.writeFileSync('scrot.png', Buffer.from(data, 'base64'));
    } catch (err) {
        console.error(err);
    } finally {
        await client.close();
    }
}).on('error', (err) => {
    console.error(err);
});