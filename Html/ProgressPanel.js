
function FUNCTION_PREFIX_Start()
{
    var iFrame = document.createElement('iframe');
    iFrame.setAttribute('style', 'position: absolute; left: -100px; top: -100px; width: 10px; height: 10px; overflow: hidden;');
    document.getElementsByTagName('body')[0].appendChild(iFrame);
    iFrame.src = '{URL}';
}

function FUNCTION_PREFIX_Update(data)
{
    main = document.getElementById('{ID}'); // .style.width = data.percent + '%';

    inner = main.getElementsByTagName('{CHILD}')[0];
    inner.style.width = data.percent + '%';
    inner.innerHTML = data.text;
}

function FUNCTION_PREFIX_Finish()
{
    document.getElementById('{id}').style.width = '100%';

    document.getElementById('pg-text-1').innerHTML = 'Demo done';
    document.getElementById('pg-text-2').innerHTML = 'Demo done';
}

// FUNCTION_PREFIX_Start();
