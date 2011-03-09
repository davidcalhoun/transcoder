onmessage = function(s) {
    var hex = Number(s.data).toString(16);
    padding = typeof (padding) === "undefined" || padding === null ? padding = 2 : padding;

    while (hex.length < padding) {
        hex = "0" + hex;
    }

    postMessage(hex);
}