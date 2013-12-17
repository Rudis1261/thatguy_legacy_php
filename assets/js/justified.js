var lastWidth = 0;
var photo_array = '';

// only call this when either the data is loaded, or the windows resizes by a chunk
function f()
{    
    lastWidth = $("div#justified").innerWidth();
    $("div.picrow").width(lastWidth);
    processPhotos(photo_array);
    $(".loading").remove();
}


if ($('.page_width').length > 0) {
    var getWidth = $('.page_width').attr('data-value');
    var getId = $('.page_id').attr('data-value');    
    var getCurrentWidth = $(".container").width();
    
    // Is the width Set?
    if (getWidth == 'false') {	
	redirectWithWidth(getId);
    
    } else {
	
	// Is the width still correct?
	if (getWidth != getCurrentWidth) {
	    redirectWithWidth(getId);
	}
    }
}


// Redirect the page with the correct with
function redirectWithWidth(id) {
    var getCurrentWidth = $(".container").width();
    window.location = "?action=view&id=" + id + "&width=" + getCurrentWidth;
}

// When the document has finished loading
$(document).ready(function() {
   
    var portfolioPage = $("div#justified").attr('data-value'); 	 
    /*$("div#justified").prepend("<div class='loading alert alert-info'>Loading images, please wait....</div>");   
    $.getJSON("portfolio.php", { action: "ajax", id: portfolioPage }, function(data, status) {	
	photo_array = data;
	f();
    });*/
    
    // Detect the window resize
    $(window).resize(function() { 
        var nowWidth = $("div#justified").innerWidth();
        
        // test to see if the window resize is big enough to deserve a reprocess
        if( nowWidth * 1.1 < lastWidth || nowWidth * 0.9 > lastWidth )
        {            
	    f();
        }
    });	
    
    // Detect a key press and then action it
    $(document).keyup(function(e) {
	if (e.keyCode == 27) { destroyImage(); }   // esc
    });
    
});

// This function will allow me to create a image gallery
function previewImage(img, large, comment, title)
{
	// Get the original size
	var getWidth = $(document).width();
	var getHeight = $(document).height();
	
	// Get the new size
	var newWidth = Math.floor(getWidth * 0.8);
	var newHeight = Math.floor(getHeight * 0.8);
	var offsetX = Math.floor(newWidth / 2);
	var top = Math.floor((getHeight - newHeight) / 2) + "px";
	
	var closeBtn = $('<a/>', {
								id: "fancybox-close",
								href: '#'
					    	}).css({
					    		"display": "inline"
					    	});
					    	
	// Bind the Close
    closeBtn.bind({
		click: function() { destroyImage(); }
    }); 			  
					    	
    var Element = $('img[src="' + img + '"]');
    var background = $('<div/>', {	class : "holder-background" });
    var thumbNail = $('<div/>', {	class : "thumbnail holder",
									html : '<div width="' + newWidth + '" height="' + newHeight + '" style="border: 1px solid grey; width: ' + newWidth + 'px; height: ' + newHeight + 'px; overflow: hidden; "><img width="' + newWidth + '" src="' + large + '" alt="' + title + '" /></div>',
									width : newWidth,										
									height : newHeight,	
																					
								}).css({ 
									'margin-left' : -offsetX, 
									'top' : top,									
								}).append(closeBtn);
    
    // Bind the Thumbnail
    thumbNail.bind({
		click: function() { 
			var newtab = window.open();
			newtab.location = large;
		}
    });  
    
    // Bind the background
    background.bind({
		click: function() { destroyImage(); }
    });  
    
    // Append everything  
    $('body').append(background);
    var holder = $('body').append(thumbNail).hide().fadeIn('slow');
}




// Destroy the image
function destroyImage() {
	$('.holder-background').fadeOut('slow').remove();
	$('.holder').fadeOut('slow').remove();
}




// When hovering over the image I would like it to also do something
function hoverImage(img, comment, heading)
{
    if (comment == '')
    {
		var show = 60;
    } else {
		var show = 90;
    }
        
    // Replace nulls
    if (comment == null) { comment = ''; }
    if (comment == '') { comment = ''; }
    if (heading == null) { heading = 'No-name'; } 
    if (heading == '') { heading = 'No-name'; } 
    
    var Element = $('img[src="' + img + '"]');
    var offset = Element.offset();
    var top = offset.top + Element.height() - show;
    var left = offset.left;
    var wt = Element.width();
    var ht = show;  
    var innerStyle = "style='background: black; padding: 5px; padding-left: 10px; margin: 0px;'";
    var holder = $('<div/>', {class: "overlay",
			    html: "<div " + innerStyle + "><h4>" + heading + "</h4>" + comment + "</div>",	    
			    }).css({'background-color': 'white',
				   'top': (top + ht + 3),
				   'left': (left),
				   'position': 'absolute',
				   'border-left' : '1px solid #ccc',
				   'border-right' : '1px solid #ccc',				   
				   'border-bottom' : '1px solid #ccc',
				   'z-index': 1000,
				   'padding' : '0px 4px 4px 4px',
				   'color': 'white',
				   'font-size': '12px',
				   'overflow': 'hidden',
				    "cursor": "pointer"}).width(wt);        
    $('body').append(holder).fadeIn();
    
}

function processPhotos(photos)
{  
    
    // divs to contain the images
    var d = $("div.picrow");
    
    // get row width - this is fixed.
    var w = d.eq(0).innerWidth();
    
    // initial height - effectively the maximum height +/- 10%;
    var h = 200;
    
    // margin width
    var border = 5;
    
    // We need padding as well for the image decoration   
    var padding = 5;    
        
    // store relative widths of all images (scaled to match estimate height above)
    var ws = [];
    $.each(photos, function(key, val) {	
        var wt = val.width;
        var ht = val.height;
        if( ht != h ) { wt = Math.floor(wt * (h / ht)); }
        ws.push(wt);	
    });

    // total number of images appearing in all previous rows
    var baseLine = 0; 
    var rowNum = 0;
    
    while(rowNum++ < d.length)
    {
        var d_row = d.eq(rowNum-1);
        d_row.empty();
        
        // number of images appearing in this row
        var c = 0; 
        // total width of images in this row - including margins
        var tw = 0;
        
        // calculate width of images and number of images to view in this row.
        while( tw * 1.1 < w)
        {
            tw += ws[baseLine + c++] + border * 2 + padding * 2;
        }
    
        // Ratio of actual width of row to total width of images to be used.
        var r = w / tw; 
        
        // image number being processed
        var i = 0;
        // reset total width to be total width of processed images
        tw = 0;
        // new height is not original height * ratio
        var ht = Math.floor(h * r);
        while( i < c )
        {
	    var photo = photos[baseLine + i];	   
	    if (photo !== undefined)
	    {
		// Calculate new width based on ratio
		var wt = Math.floor(ws[baseLine + i] * r);
		// add to total width with margins
		tw += wt + border * 2 + padding * 2;
		// Create image, set src, width, height and margin
		(function() {
		    var img = $('<img/>', {class: "photo img img-polaroid",
					    src: photo.src,
					    width: wt,
					    height: ht,					    
					    alt: photo.title,
					    "data-comment": photo.comment,
					    "data-title": photo.title }).css({"margin": border + "px", "cursor": "pointer" });
		    //portfolioImages.push(photo.large); 
		    var url = photo.large;
		    var thumb = photo.src;
		    var comment = photo.comment;
		    var title = photo.title;
		    img.bind({
				click: function() {
					previewImage(thumb, url, comment, title);
				},
				mouseleave: function() {
					$('.overlay').hide().remove();
				},
				mouseenter: function() {
					hoverImage(thumb, comment, title);
				}
		    });
		    d_row.append(img);
		})();		
	    }
	    i++;
        }	
        
        // if total width is slightly smaller than 
        // actual div width then add 1 to each 
        // photo width till they match
        i = 0;
        while( tw < w )
        {
            var img1 = d_row.find("img:nth-child(" + (i + 1) + ")");
            img1.width(img1.width() + 1);
            i = (i + 1) % c;
            tw++;
        }
        // if total width is slightly bigger than 
        // actual div width then subtract 1 from each 
        // photo width till they match
        i = 0;
        while( tw > w )
        {
            var img2 = d_row.find("img:nth-child(" + (i + 1) + ")");
            img2.width(img2.width() - 1);
            i = (i + 1) % c;
            tw--;
        }
        
        // set row height to actual height + margins
        d_row.height(ht + border * 2 + padding * 2);    
        baseLine += c;
    }
    //console.log(portfolioImages);
}
