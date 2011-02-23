onmessage = function(s) {
    var minitext = s.data;
    minitext = minitext.replace(/\/\*[\s\S]*?\*\//g, ""); //delete comments enclosed in /* */
    minitext = minitext.replace(/[\n]+/g, "");          //delete all line returns
    minitext = minitext.replace(/[\n\n]+/g, '\n');      //delete excessive line returns
    minitext = minitext.replace(/[\t]+/g, "");          //delete tabs
    minitext = minitext.replace(/[\s\s]+/g, " ");       //smallify multiple spaces, turn it into one space
    minitext = minitext.replace(/\s{/g, "{");           //remove space between selectors and left braces (i.e. "body {" -> "body{")
    
    postMessage(minitext);
}