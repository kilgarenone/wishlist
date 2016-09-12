



$(document).ready(function(){
	
	/**
	 * [d Get today's date in YYYY-MM-DD format]
	 * @type {Date}
	 */
	var d = new Date(),
		month = d.getMonth()+1,
		day = d.getDate(),
		today = d.getFullYear() + '-' +
		    (month<10 ? '0' : '') + month + '-' +
		    (day<10 ? '0' : '') + day;

	


	$("#searchButton").click(function(event){
		event.stopPropagation(); 
			
		$('.movie').empty();
		
		var init = setup();
		init();
	});


	$('#searchBox').keypress(function(event){
		var keycode = event.keyCode || event.which;

    	if(keycode == '13'){
        	$('#searchButton').click();   
    	}
	});



	$('.movie').on('click', '.wish_button', function() {
		
		var btnID = $(this).attr('id'),
			typeRegex = /^(\w+)\.(\w*\d+)$/,
			reArray = typeRegex.exec(btnID),
			type = reArray[1],
			id = reArray[2];
		 	console.log('pressedid=' + id);
	
		 switch(type){
		 	case "movie" :
			 	movie_info = key.get_TomatoInfo() + id + ".json" + "?apikey=" + key.get_TomatoKey();
				$.ajax({
		    		url: movie_info,
		    		dataType: "jsonp",
		    		success: getDirector
			  	});
			  	break;
			
			 case "music" :
			 	$.ajax({
					url:"/php/rovi.php",
					type:"POST",
					dataType: "json",
					data: {'query': id, "state": 'buttonPressed'}
					//success: getAlbums	
				});
			  	break;
		  }		
	});



	function getAlbums(discos){
		
		$.each(discos, function (index, disco){
			var container = $('<div/>').attr('class','wish');
				title = disco.title,
				amgReleaseDate = disco.releaseDate,
				amgID = disco.amgID,
				releaseArr = disco.releases,
				image = releaseArr[0].imageUrl,
				itunesObj = {};


			getItunes(amgID, function(data){
					if(data.results.length){
						var itunes = data.results[0];
						itunesObj.price = itunes.collectionPrice;
						itunesObj.currency = itunes.currency;
						
						container.append('<h4>' + "iTunes: " + itunesObj.currency + " "+ itunesObj.price + '</h4>');
				}
			});	

			container.append('<h4>' + title + '</h4>')
		 		 	 .append('<img src="' + image + '" />');

			$.each(releaseArr, function (index, amazonRelease) {
				var asin = amazonRelease.ASIN,
					price = amazonRelease.price,
					type = amazonRelease.type;
					
				container.append('<h5>' + "Price: " + price)
						.append('<h5>' + "Type: " + type);

				if(Date.parse(amgReleaseDate) > Date.parse(today)){
					container.append('<h5>' + "Pre-order! Release date: " + amgReleaseDate);
				}
			});

			$('.movie').append(container);

		});
	}



	function getItunes(amgID, callback){
		var itunesSearch = "https://itunes.apple.com/lookup?amgAlbumId=" + amgID + "&callback=?";

		$.ajax({
    		url: itunesSearch,
    		dataType: "json",
    		success: function(data){
    			if(typeof callback === "function"){
    			 	callback(data);
    			 }
    		 }
	  	});
	}






	function getDirector(movieInfo){
		var director = movieInfo.abridged_directors[0].name,
			title = movieInfo.title,
			image = movieInfo.posters.original,
			releaseDate = movieInfo.release_dates.theater;


		var amazon = $.ajax({
			url:"/php/amazon.php",
			type:"POST",
			dataType: "json",
			data: {'search': title , 'director': director}	
		});

		$.when(amazon).done(function(dvds){
			
			makeMovie(image, title, releaseDate, dvds);
			
		});
	}



	function makeMovie(image, title, releaseDate, dvds){
		var container = $('<div/>').attr('class','wish');
			
		
		container.append('<h4>' + title + '</h4>')
		 		 .append('<img src="' + image + '" />');

		 if(dvds !== null){
			 $.each(dvds, function (index, dvd){

			 	if(dvd.price !== null){
			 		container.append('<h5>' + dvd.price + '</h5>')
			 	}

			 	if(dvd.releaseDate !== null){
			 		
			 		if(Date.parse(releaseDate) > Date.parse(dvd.releaseDate)){
			 			container.append('<h5>' + "Premiere:" + releaseDate);
			 			return false;
			 		}
			 		else{
			 			container.append('<h5>' + dvd.type + '</h5>');
			 		}
			 	}
		 	});
		}else{
			if(Date.parse(releaseDate) >= Date.parse(today)){
			 	container.append('<h5>' + "Premiere: " + releaseDate);
			 }
		}
		  		 
		$('.movie').append(container);
	}

	

	var setup = function(){
		
		query = $("#searchBox").val(),
		queryEncoded = encodeURIComponent(query).replace(/[!'()]/g, escape).replace(/\*/g, "%2A"),
		moviesSearchUrl = key.get_TomatoSearch() + key.get_TomatoKey();
		
		return function(){ 	
			$.ajax({
	    		url: moviesSearchUrl + '&q=' + queryEncoded + '&page_limit=3' + '&page=1',
	    		dataType: "jsonp",
	    		success: getTomato
	  		});	

			
			 $.ajax({
					url: "/php/rovi.php",
					type:"POST",
					dataType: "json",
					data: {'query': queryEncoded, 'state': 'search'},
					success: makeArtist		
			}); 	
		};
	}


	function makeArtist(artisan){
		var item = {
					artistName: artisan.name,
					id: "music."+ artisan.id,
					imageUrl: artisan.image,
					releaseDate: artisan.upcomingRelease
			};

			buildItem(item);

			$.ajax({
				url:"/php/rovi.php",
				type:"POST",
				// dataType: "json",
				data: {'query': artisan.id, "state": 'cache'},
			});

		//console.log(artisan.id);
	}



	function getTomato(tomatoes){
		console.log(tomatoes);
		if(tomatoes.total == 0){
			return;
		}

		var tomatoes = tomatoes.movies;

		$.each(tomatoes, function (index, tomato){
			var item = {
					title: tomato.title,
					imageUrl: tomato.posters.original,
					releaseDate: tomato.release_dates.theater,
					id: "movie."+ tomato.id
			};

			buildItem(item);
		});
	}	




	var key = function () {

		var tomato = "mqxz3jc534693x2rd3yqkyek",
			movieSearch = "http://api.rottentomatoes.com/api/public/v1.0/movies.json?apikey=",
			movieInfo = "http://api.rottentomatoes.com/api/public/v1.0/movies/";

	     return {
	          get_TomatoKey: function () {
	               return tomato;
	          },
	          get_TomatoSearch: function () {
	               return movieSearch;
	          },
	          get_TomatoInfo: function () {
	               return movieInfo;
	          }
	     };
	}();
	

	function buildItem(item){

		var itemNew = $.extend({
			price: "",
			binding:"",
			releaseDate: "",
			artistName: ""
		}, item);


		var container = $('<figure/>',
						{
							class: 'items'
						}),
			wishListButton = $('<input>', {
							class: 'wish_button',
							id: itemNew.id,
							type: 'button',
							style: 'opacity:0',
							value: 'Add to Wishlist'
						}),
			image = $('<img/>',
					{
						src: itemNew.imageUrl
					}),
			caption = $('<figcaption/>',{
						class: 'itemCaption',
						text: itemNew.artistName + ' - Next release: ' + itemNew.releaseDate,
						style: 'display:None'
					});


		// To-do: makes release datas display conditional	

		container.append(image)
		  		 .append(caption)
				 // .append('<h5>' + itemNew.price + '</h5>')
		  	// 	 .append('<h6>' + itemNew.binding + '</h6>')
				 .append(wishListButton);

		$('.movie').append(container);
	}


	
	$('.movie').hoverIntent({
		over: showInfoBig,
		out: hideInfoSmall,
		timeout:200,
		interval: 200,
		selector: '.items'
	});
	
	function showInfoBig(){
		var self = $(this);
		$('.itemCaption', self).stop().fadeIn(100, 'easeInSine', function(){
			$('.wish_button', self).animate({opacity: 1}, 500, 'easeInBack');
		});
	}
	
	function hideInfoSmall(){
		var self = $(this);
		$('.wish_button', self).stop().animate({opacity: 0}, 500, 'easeOutBack', function(){
			$('.itemCaption', self).fadeOut(500, 'easeOutSine');
		});
	}

	

	$("#signup_form").submit(function(event) {
		event.preventDefault();

		var form = $(this);

		if(!form.hasClass("shown")){
			$( "div.join" ).slideDown(400, function(){
			  	$('form :text:first').focus();
				form.addClass('shown');
			});
		}
		else{
			validateForm(form);
			console.log('called validateform function');
		}
	});       
	

	function validateForm(form){
		var formInstance = form.parsley();

	    if (formInstance.validate('block1', true)){
	    	$.post("php/registration.php", form.serialize());
			form.removeClass('shown');
			console.log('valid form');	
	    }
	    else{
	    	console.log('invalid form');
	    	return;
	    }		
	}



});


window.ParsleyConfig = {
	// successClass: 'success',
 // 	errorClass: 'error',
  	errorsWrapper: '<span class=\"errorMessage\"></span>',
  	errorTemplate: '<span></span>'
};



	
