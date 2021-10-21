// Set default spectrum elements
$(".blankSquareColor").spectrum({
  color: "#FFFFFF",
  change: function (color) {
    $(".unfilled").css("background-color", color.toHexString());
    $(".blankSquareColor").val(color.toHexString());
  },
});

$(".letterSquareColor").spectrum({
  color: "#EEEEEE",
  change: function (color) {
    $(".filled").css("background-color", color.toHexString());
    $(".letterSquareColor").val(color.toHexString());
  },
});

$(".letterColor").spectrum({
  color: "#000000",
  change: function (color) {
    $(".filled").css("color", color.toHexString());
    $(".letterColor").val(color.toHexString());
  },
});

$(".lineColor").spectrum({
  color: "#000000",
  change: function (color) {
    $(".filled").css("border", "2px solid " + color.toHexString());

    // Only change hidden lines if they're showing - need to remain white for copy and pasting to word if hidden
    if ($(".unfilled").css("visibility") === "visible") {
      $(".unfilled").css("border", "2px solid " + color.toHexString());
    }

    $(".lineColor").val(color.toHexString());
  },
});

$(".skeleton").css(
  "border",
  "2px solid " + $(".lineColor").spectrum("get").toHexString()
);

// Updates the solution section to hidden/visable on check box update
function solutionCheckboxChange() {
  if ($(".showSolutionCheckbox").is(":checked")) {
    $(".solutionSection").show();
  } else {
    $(".solutionSection").hide();
  }
}

// Updates the solution section to hidden/visable on check box update
function blankSquareCheckboxChange() {
  if ($(".showBlankSquaresCheckbox").is(":checked")) {
    $(".unfilled").css("visibility", "visible");
    $(".unfilled").css(
      "border",
      "2px solid " + $(".lineColor").spectrum("get").toHexString()
    );
  } else {
    $(".unfilled").css("visibility", "hidden");
    $(".unfilled").css("border", "0px solid #FFFFFF"); //+ $(".lineColor").spectrum('get').toHexString());
  }
}
