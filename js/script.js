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
//added script to take screenshots for PPT generation
$("#generatePPT").click(function () {
  //Display all the solutions because if not the screenshot cannot be taken
  if ($(".accordion-button").hasClass("collapsed")) {
    $(".accordion-button").click();
  }

  let data = {
    skeletons: new Array(),
    solutions: new Array(),
  };

  const skeletons = document.querySelectorAll(".skeleton");
  const solutions = document.querySelectorAll(".skeleton-solution");

  for (let i = 0; i < skeletons.length; i++) {
    html2canvas(skeletons[i]).then((canvas) => {
      // image data (base-64 string)
      const dataurl = canvas.toDataURL("image/jpeg");
      // console.log(dataurl);
      let skeleton = new Object();
      skeleton.image = dataurl;
      //reducing the size to 70% of the original size
      skeleton.width = canvas.width * 0.7;
      skeleton.height = canvas.height * 0.7;
      data.skeletons.push(skeleton);
    });
  }

  for (let i = 0; i < solutions.length; i++) {
    html2canvas(solutions[i]).then((canvas) => {
      // image data (base-64 string)
      const dataurl = canvas.toDataURL("image/jpeg");
      let solution = new Object();
      solution.image = dataurl;
      solution.width = canvas.width * 0.7;
      solution.height = canvas.height * 0.7;
      data.solutions.push(solution);

      //when it finishes taking all the screenshots, send the data to the backend
      if (i == solutions.length - 1) {
        // console.log(data);
        sendForm(JSON.stringify(data));
      }
    });
  }
});

//get the solutions and skeletons as 'data' encoded in JSON, and send the POST request to the GeneratePPT.php file
//if the request is successful, send another GET request to download.php, adding the filename as parameter to download the PPT
function sendForm(data) {
  $.ajax({
    type: "POST",
    url: "GeneratePPT.php",
    data: data,

    success: function (data) {
      // console.log(data);
      window.location = "download.php?filename=" + data;
    },
  });
}