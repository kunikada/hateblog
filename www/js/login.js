(function() {
    var count = 0;
    var msg = "登録をやり直してください";
    LoginObject = {
        dont_give_up: function() {
            var reminder = document.getElementById("reminder");
            count++;
            if (count == 9) {
                msg = "Twitterでご連絡ください";
                reminder.href = "http://twitter.com/_hateblog/";
                reminder.onclick = "";
            }
            this.innerContent(reminder, msg);
        },
        toggle: function() {
            var submit = document.getElementById("submit");
            var toggle = document.getElementById("toggle");
            var loginForm = document.getElementById("loginForm");
            count = 0;
            var submitLabel = submit.value;
            var toggleLabel = this.innerContent(toggle);
            submit.value = toggleLabel;
            submit.title = toggleLabel;
            this.innerContent(toggle, submitLabel);
            toggle.title = submitLabel+"はこちらから";
            var uri = loginForm.action.indexOf("/user/login") >= 0 ? "/user/register" : "/user/login";
            loginForm.action = uri;
        },
        innerContent: function(e, val) {
            if (typeof e.innerText != "undefined") {
                if (typeof val == "undefined") {
                    return e.innerText;
                }
                e.innerText = val;
            } else {
                if (typeof val == "undefined") {
                    return e.textContent;
                }
                e.textContent = val;
            }
        }
    }
})();
