// shorthand for returning HTML elements
function $el(id) {
	return document.getElementById(id);
}

// switches task types between progress (x out of y) and checkable (either is done or not)
function task_type(check) {
	$el("type_prog").style.display = (check ? "none" : "inline");
	$el("type_check").style.display = (check ? "inline" : "none");
	$el("form_type").value = (check ? "1" : "0");
	return false;
}

// disallows empty task names
function name_val(field) {
	if (field.value.replace(/\s/g, "").length === 0) {
		field.value = "Unnamed task";
	}
}

// round decimals and adjust range errors
function progress_val(field) {
	field.value = Math.round(field.value);
	if (field.value < 0) {
		field.value = 0;
	}
	if (parseInt($el("cur").value) > parseInt($el("tot").value)) {
		if (field === $el("cur")) {
			$el("tot").value = $el("cur").value;
		} else {
			$el("cur").value = $el("tot").value;
		}
	}
}

// submit the form (yay!)
function form_submit(full) {
	var errors = [];
	if (full) {
		if ($el("name").value === "") {
			errors.push("This task doesn't have a name.");
		}
		var check = $el("form_type").value === "1";
		if (check) {
			$el("cur").value = ($el("done").checked ? "1" : "0");
			$el("tot").value = "1";
		} else if (parseInt($el("cur").value) > parseInt($el("tot").value)) {
			errors.push("The current value, " + $el("cur").value + ", exceeds the total value, " + $el("tot").value + ", for the task \"" + $el("name_" + i).value + "\".");
		}
	}
	if (errors.length === 0) {
		$el("form_val").value = "1";
		return true;
	} else {
		alert(errors.join("\n"));
	}
	return false;
}

// disable enter key to submit
document.onkeypress = function(evt) {
	var evt = (evt) ? evt : ((event) ? event : null);
	var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
	if ((evt.keyCode == 13) && (node.type == "text")) {
		return false;
	}
}
