document.getElementById("buttonClear").onclick = function () {
    d.clear();
};

document.getElementById("buttonShowGrid").onclick = function () {
    vector = d.calculate(true);
    console.log("vector: ", vector);
};

document.getElementById("buttonGetNumber").onclick = function () {
    let http = new XMLHttpRequest();
    let vector = d.calculate(true);
    let data = new FormData();

    http.open('POST', 'test_numbers_check.php', true);
    http.onreadystatechange = function () {//Call a function when the state changes.
        if (http.readyState == 4 && http.status == 200) {
            //var responseObject = JSON.parse(http.responseText);
            console.info('Response: ', http.responseText);
        }
        /*
        else {
            console.error('Request failed. Returned status of ' + http.status);
        }
        */
    };
    //http.send('user=person&pwd=password&organization=place&requiredkey=aaa');
    //http.send(JSON.stringify(vector));

    //console.debug('vector: ', vector);
    //console.debug('JSON.stringify(vector): ', JSON.stringify(vector));

    data.append('image', JSON.stringify(vector));
    http.send(data);
};

/*
document.addEventListener('keypress', function(e) {
    console.log(e.key.toLowerCase());
});
*/
