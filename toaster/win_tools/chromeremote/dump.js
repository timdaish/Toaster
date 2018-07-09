const CDP = require('chrome-remote-interface');
const argv = require('minimist')(process.argv.slice(2));
const file = require('fs');

// CLI Args
const url = argv.url || 'https://www.google.com';
const pathname = argv.pathname;

CDP(async(client) => {
    const {Network, Page, Runtime} = client;
    try {
        await Network.enable();
        await Page.enable();
        await Network.setCacheDisabled({cacheDisabled: true});
        await Page.navigate({url: url});
        await Page.loadEventFired();
        const result = await Runtime.evaluate({
            expression: 'document.documentElement.outerHTML'
        });
        const html = result.result.value;
        //console.log(html);
        file.writeFile(pathname, html, function(err) {
            if (err) {
              console.error(err);
            } else {
              console.log('HTML dump saved ');
            }
            client.close();
          });
    } catch (err) {
        console.error(err);
    } finally {
        client.close();
    }
}).on('error', (err) => {
    console.error(err);
});