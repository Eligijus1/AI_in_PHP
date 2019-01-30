document.getElementById("buttonClear").onclick = function () {
    d.clear();
};

document.getElementById("buttonShowGrid").onclick = function () {
    vector = d.calculate(true);
    console.log("vector: ", vector);
};

document.getElementById("buttonGetNumber").onclick = function () {
    let http = new XMLHttpRequest();
    let params = 'orem=ipsum&name=binny';
    http.open('POST', 'test_numbers_check.php', true);

    http.onreadystatechange = function () {//Call a function when the state changes.
        if (http.readyState == 4 && http.status == 200) {
            var responseObject = JSON.parse(http.responseText);
            console.info('Response: ', responseObject);
        }
        /*
        else {
            console.error('Request failed. Returned status of ' + http.status);
        }
        */
    };
    http.send(params);
};

/*
document.addEventListener('keypress', function(e) {
    console.log(e.key.toLowerCase());
});
*/
