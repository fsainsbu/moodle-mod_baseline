
function MySliderInit(bg, thumb, valuearea, textfield, aSliderName , mystep, mywidth, Myoff) {
    var Event = YAHOO.util.Event,
        Dom   = YAHOO.util.Dom,
        lang  = YAHOO.lang, 
		slider;

	// The slider move from 0 pixels 
    var topConstraint = 0;

    // The slider can move pixels across
    var bottomConstraint =  mywidth;

    // Custom scale factor for converting the pixel offset into a real value
    var scaleFactor = 1 / mystep;

    // The amount the slider moves when the value is changed with the arrow
    // keys
    var keyIncrement = mystep;

    Event.onDOMReady(function() {

        slider = YAHOO.widget.Slider.getHorizSlider(bg, thumb, topConstraint, bottomConstraint ,  keyIncrement );

		slider.MySliderName = aSliderName;

        slider.getRealValue = function() {
            return Math.round(this.getValue() / mystep);
        }

        slider.subscribe("change", function(offsetFromStart) {

            var valnode = Dom.get(valuearea);
            var fld = Dom.get(textfield);

            // Display the pixel value of the control
            valnode.innerHTML = offsetFromStart;

            // use the scale factor to convert the pixel offset into a real
            // value
            var actualValue = slider.getRealValue() - Myoff;

            // update the text box with the actual value
            fld.value = actualValue;
	curValue=actualValue+1;
            // Update the title attribute on the background.  This helps assistive
            // technology to communicate the state change
            Dom.get(bg).title = "slider value = " + curValue;
		var z=1;
		for (z=1;z<mystep;z++)
				if ( z == actualValue + 1) {
						showdiv(valuearea+'-'+z);
					document.getElementById(valuearea+'-'+z).style.display = "block";	
				
				} else {
						hidediv(valuearea+'-'+z);
					document.getElementById(valuearea+'-'+z).style.display = "none";	
				}

			Dom.get("sliderName").innerHTML = this.MySliderName;
        });

        slider.subscribe("slideStart", function() {
                //YAHOO.log("slideStart fired", "warn");
            });

        slider.subscribe("slideEnd", function() {
                //YAHOO.log("slideEnd fired", "warn");
            });

        // set an initial value
            var fld = Dom.get(textfield);
        // mystart=fld.value;
         //if( fld.value <= 0 ){
//error in setting is here  FPS or its beeing overwritten
            mystart = (fld.value + Myoff) * mystep;
          //}
        slider.setValue(mystart);
		// fld.value= mystart - Myoff;
            Dom.get(bg).title = "slider value = " + mystart;

        // Listen for keystrokes on the form field that displays the
        // control's value.  While not provided by default, having a
        // form field with the slider is a good way to help keep your
        // application accessible.
        // Event.on(textfield, "keydown", function(e) {

            // set the value when the 'return' key is detected
            // if (Event.getCharCode(e) === 13) {
                // var v = parseFloat(this.value, 10);
                // v = (lang.isNumber(v)) ? v : 0;

                // convert the real value into a pixel offset
                // slider.setValue(:wqMath.round(v/scaleFactor));
            // }
       // });
        
        // Use setValue to reset the value to white:
//        Event.on("putval", "click", function(e) {
 //           slider.setValue(100, false); //false here means to animate if possible
  //      });
        
        // Use the "get" method to get the current offset from the slider's start
        // position in pixels.  By applying the scale factor, we can translate this
        // into a "real value
   //     Event.on("getval", "click", function(e) {
    //        YAHOO.log("Current value: "   + slider.getValue() + "\n" + 
     //                 "Converted value: " + slider.getRealValue(), "info", "example"); 
      //  });
 });

	return slider;

};
