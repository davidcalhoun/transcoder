onmessage = function(s) {
    postMessage(s.data.replace(/([&<>])/g,
        function (c) {
            return "&" + {
                "&": "amp",
                "<": "lt",
                ">": "gt"
            }[c] + ";";
        })
    );
}