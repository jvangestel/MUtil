
function FUNCTION_PREFIX_Start()
{
    var iFrame = document.createElement('iframe');
    iFrame.setAttribute('style', 'position: absolute; left: -100px; top: -100px; width: 10px; height: 10px; overflow: hidden;');
    // iFrame.setAttribute('style', 'position: absolute; left: 0px; top: 0px; width: 100px; height: 100px; overflow: hidden;');
    document.getElementsByTagName('body')[0].appendChild(iFrame);
    iFrame.src = '{URL_START}';
}

function FUNCTION_PREFIX_Update(data)
{
    main = jQuery("#{ID}");
    main.progressbar( "option", "value", data.percent);
    main.progressbar({value: data.percent});

    inner = main.find('{TEXT_TAG}.{TEXT_CLASS}');
    if (inner) {
        text = data.percent + '%';
        if (data.text) {
            text = text + ' ' + data.text;
        }
        inner.html(text);
    }
}

function FUNCTION_PREFIX_Finish()
{
    main = jQuery("#{ID}");
    main.progressbar( "option", "value", 100);

    inner = main.find('{TEXT_TAG}.{TEXT_CLASS}');
    if (inner) {
        inner.empty();
        inner.append('100% Done!');
    }

    url = '{URL_FINISH}';
    if (url.length > 0) {
        location.href = url;
    }
}


if (__AUTOSTART__) {
    jQuery().ready(FUNCTION_PREFIX_Start());
}