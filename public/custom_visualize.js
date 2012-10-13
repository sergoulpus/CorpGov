/*
 *	 Original comment:
 *  This file contains the JS that handles the first init of each jQuery demonstration, and also switching
 *  between render modes.
 */
jsPlumb.bind("ready", function() {
	// chrome fix.
	document.onselectstart = function () {
		return false;
	};				
	CGD.init();	// Corporate Governance Diagram
       
});


(function() {
	window.CGD = {
		init : function() {
			jsPlumb.importDefaults({
				DragOptions : {
					cursor: "pointer", 
					zIndex:2000
				}	,
				Connector: 'Straight',
				Endpoint: 'Blank',
				PaintStyle: {
					strokeStyle:"black", 
					lineWidth: 1
				},
				Container: $("body")
			});
	
			//jsPlumb.Defaults.Container = $("body");
			
			var outlines = new Array("boxOmanikud","boxNoukogu","boxJuhatus", "boxOrganisatsioon");
			for(x = 0; x < outlines.length -1; x++) {
				jsPlumb.connect({  
					source: outlines[x],
					target:outlines[x+1], 
					anchors:["BottomCenter", "TopCenter"]
				});            
			}
		}
	};	
})();


$(function(){
	c=0;
	
	$('.subbox[softcon]').each(function(){
		$(this).css('color', 'blue');
		
				jsPlumb.connect({  
					source: $(this).attr('id'),
					target: $(this).attr('softcon'), 
					anchors:["LeftMiddle", "LeftMiddle"],
					connector:[ "Bezier", { curviness: 50+(c++)*20 }],
					paintStyle: {
						dashstyle:"2",
						strokeStyle:"blue",
						lineWidth: 1
					}
				});            
	});

	$('.subbox[hardcon]').each(function(){
			
				$(this).css('color', 'green');
				$('#'+$(this).attr('hardcon')).css('color', 'green');
		
				jsPlumb.connect({  
					source: $(this).attr('id'),
					target: $(this).attr('hardcon'), 
					anchors:["RightMiddle", "RightMiddle"],
					connector:[ "Bezier", { curviness: 50+(c++)*20 }],
					paintStyle: {
						strokeStyle:"green",
						lineWidth: 1
					}
				});            
	});
});