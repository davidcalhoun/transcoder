onmessage = function(s) {
    postMessage(s.data.replace(/<[^>]+>/gi,""));
}