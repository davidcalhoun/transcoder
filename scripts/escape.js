onmessage = function(s) {
    postMessage(escape(s.data));
}