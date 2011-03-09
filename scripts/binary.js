onmessage = function(s) {
    postMessage(parseInt(s.data).toString(2));
}