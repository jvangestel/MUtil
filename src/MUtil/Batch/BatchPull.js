
/*jslint browser: true, unparam: true */

// Creating the widget
jQuery.widget("ui.pullProgressPanel", {

    // default options
    options: {
        // finishUrl: the request url
        // formId: an optional form id for post parameters
        // panelId: text id:,
        // runUrl: the request url
        // targetId: search for the element whose content is replaced
        /**
         * Template for progress message available replacement vars: 
         * {total}      Total time
         * {elapsed}    Elapsed time
         * {remaining}  Remaining time
         * {percent}    Progress percent without the % sign
         * {msg}        Message reveiced
         */
        template: "{percent}% {msg}",
        timeout: 60000

        
    },

    _init: function () {
        "use strict";

        this.progressTarget = jQuery(this.options.panelId);
        if (this.progressTarget.length) {
            this.textTarget = this.progressTarget.find(this.options.targetId);
            // this.textTarget = this.find(this.options.targetId);

            if (this.textTarget.length) {
                this.start();
            } else {
                alert('Did not find the text element: "' + this.options.targetId + '" in element id: "' + this.options.panelId + '".');
            }
        } else {
            alert('Did not find the panel id: "' + this.options.panelId + '".');
        }
    },

    complete: function (request, status) {
        "use strict";

        this.request = null;
    },

    error: function (request, status, error) {
        "use strict";

        var message;

        // alert('Communication error: ' + status);
        if (request.responseText) {
            message = request.responseText;
        } else if (error) {
            message = error;
        } else {
            message = 'No information was returned by the server.';
        }
        this.progressTarget.after('<h3>Communication error</h3><p><strong>' + status + '</strong><br/>' + message + '</p>');
        // console.log(request, status, error);
    },

    /** 
     * Convert seconds to hh:mm:ss format.
     * @param {number} totalSeconds - the total seconds to convert
     **/
    formatTime: function (totalSeconds) {
        var hours   = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds - (hours * 3600)) / 60);
        var seconds = totalSeconds - (hours * 3600) - (minutes * 60);

        // round seconds
        seconds = Math.round(seconds * 100) / 100

        var result = (hours < 10 ? "0" + hours : hours);
        result    += ":" + (minutes < 10 ? "0" + minutes : minutes);
        result    += ":" + (seconds < 10 ? "0" + seconds : seconds);
        return result;
    },

    percent: 0,

    progressTarget: null,

    start: function () {
        "use strict";

        var fd, form, self, verb;

        if (null === this.request) {
            if (this.options.runUrl) {
                this.percent       = 0;
                this.text          = "";
                this.timeElapsed   = 0;
                this.timeRemaining = 0;
                
                fd   = null;
                self = this;
                verb = "GET";

                if (this.options.formId) {
                    form = jQuery("form#" + this.options.formId);
                    if (form.size()) {
                        fd   = form.serialize();
                        verb = "POST";
                    }
                }

                this.request = jQuery.ajax({
                    url:      this.options.runUrl,
                    type:     verb,
                    dataType: "json",
                    processData: "GET" === verb,
                    data:     fd,
                    error:    function (request, status, error) {self.error(request, status, error); },
                    complete: function (request, status) {self.complete(request, status); },
                    success:  function (data, status, request) {self.success(data, status, request); }
                });
            } else {
                alert("No runUrl specified.");
            }
        }
    },

    success: function (data, status, request) {
        "use strict";

        var form;

        if (!data) {
            data.finished = false;
            this.percent = 'xx';
            this.text    = 'An error occured, no data was returned, check the error logs.';
        } else {
            this.percent       = data.percent;
            this.text          = data.text || '';
            this.timeElapsed   = data.timeTaken;
            this.timeRemaining = data.timeRemaining;


        }

        // console.log(data);
        if (data.finished) {
            this.percent       = 100;
            this.text          = "";
            this.timeRemaining = 0;            
        }
        
        this.update();

        if (data.finished) {
            if (this.options.formId) {
                form = jQuery("form#" + this.options.formId);
                if (form.size()) {
                    if (this.options.finishUrl.length) {
                        form.attr('action', this.options.finishUrl);
                    }
                    form.submit();
                    return;
                }
            }
            if (this.options.finishUrl.length) {
                location.href = this.options.finishUrl;
            }
        } else {
            this.request = null;
            this.start();
        }
    },

    text: "",

    timeElapsed: 0,

    timeRemaining: 0,

    update: function () {
        "use strict";

        // For some reason the next two lines are both needed for the code to work
        // this.progressTarget.progressbar("option", "value", data.percent);
        this.progressTarget.progressbar({value: this.percent});

        var txt       = this.options.template;
        var remaining = this.formatTime(this.timeRemaining);
        var elapsed   = this.formatTime(this.timeElapsed);
        var total     = this.formatTime(this.timeElapsed + this.timeRemaining);
        txt = txt.replace(/{elapsed}/g,   elapsed);
        txt = txt.replace(/{remaining}/g, remaining);
        txt = txt.replace(/{total}/g,     total);
        txt = txt.replace(/{percent}/g,   this.percent);
        txt = txt.replace(/{msg}/g,       this.text);

        this.textTarget.html(txt);
    },

    textTarget: null,

    request: null
});

function FUNCTION_PREFIX_Start() {
    "use strict";

    jQuery("{PANEL_ID}").pullProgressPanel({
        "finishUrl":        "{URL_FINISH}",
        "formId":           "{FORM_ID}",
        "panelId":          "{PANEL_ID}",
        "runUrl":           "{URL_START_RUN}",
        "targetId":         "{TEXT_ID}",
        "template":         "{TEMPLATE}"
    });
}

if (__AUTOSTART__) {
    jQuery().ready(FUNCTION_PREFIX_Start());
}
