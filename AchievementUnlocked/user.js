function $el(id) {
    return document.getElementById(id);
}
function form_submit_up() {
    if (!$el("user").value || !$el("pass").value) {
        alert("You must fill in all of the fields.");
    } else if ($el("math") && (!$el("math1").value || !$el("math2").value)) {
        alert("Stop being a robot and do the maths.");
    } else {
        var sum1 = document.getElementById("sum1").innerHTML;
        var sum2 = document.getElementById("sum2").innerHTML;
        sum1 = [parseInt(sum1.charAt(0)), parseInt(sum1.charAt(4))];
        sum2 = [parseInt(sum2.charAt(0)), parseInt(sum2.charAt(4))];
        var math1 = parseInt(document.getElementById("math1").value);
        var math2 = parseInt(document.getElementById("math2").value);
        if (sum1[0] + sum1[1] == math1 && sum2[0] * sum2[1] == math2) {
            return true;
        } else {
            alert("Your ability to complete maths sums is terrible.  Learn to count.");
        }
    }
    return false;
}
function form_submit_pp() {
    if (!$el("old").value || !$el("new").value || !$el("conf").value) {
        alert("You must fill in all of the fields.");
        return false;
    } else if ($el("new").value !== $el("conf").value) {
        alert("The two passwords don't match.");
        return false;
    }
    return true;
}