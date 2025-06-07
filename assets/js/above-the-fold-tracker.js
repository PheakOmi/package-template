window.addEventListener(
    'DOMContentLoaded', function () {
        var links = document.getElementsByTagName('a');
        var width = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var height = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        console.log('Viewport width:', width, 'height:', height);
        var hrefs = [];
        var seen = {};
        for (var i = 0; i < links.length; i++) {
            var rect = links[i].getBoundingClientRect();
            var visibleHeight = Math.min(rect.bottom, height) - Math.max(rect.top, 0);
            var isAboveFold = visibleHeight >= rect.height / 2 && rect.left < width && rect.right > 0;
            if (isAboveFold && !seen[links[i].href]) {
                console.log('Link:', links[i].href);
                hrefs.push(links[i].href);
                seen[links[i].href] = true;
            }
        }
        var data = {
            hrefs: hrefs,
            screen: { width: width, height: height }
        };
        console.log('Data:', data);
        console.log('WPPageFoldTracker:', WPPageFoldTracker);
        if (hrefs.length && typeof WPPageFoldTracker !== 'undefined') {
            fetch(
                WPPageFoldTracker.restUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': WPPageFoldTracker.nonce
                    },
                    body: JSON.stringify(data)
                }
            );
        }
    }
); 
