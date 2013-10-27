(function() {
	var all_engines = [ "Alla", "Google", "Bing", "Yahoo" ];

	/*
	 * Set the content of the result div
	 */
	function setResult(elm) {
		$("#result").empty();
		$("#result").append(elm).hide().fadeIn();
	}

	/*
	 * Create a new img tag with the throbber and add it the element with id
	 */
	function showThrobber(id) {
		var throbber = $("<img></img>").attr("src", "img/throbber.gif").hide();
		$(id).append(throbber);
		throbber.fadeIn();
		return throbber;
	}

	function hideThrobber(img) {
		$(img).fadeOut();
	}

	/*
	 * Update which tab is selected
	 */
	function updateTabSelection(engine) {
		if (engine == "") {
			engine = "Alla";
		}
		/* Reset selection */			
		for (var i = 0; i < all_engines.length; i++) {
			$("#" + all_engines[i]).removeClass("selected");
		}
		/* Mark as selected */			
		$("#" + engine).addClass("selected");
	}
	
	/*
	 * Show error message
	 */
	function showFailedAjax() {
		var msg = $("<h3></h3>").text("Connection to server failed. Try again.");
		setResult(msg);	
	}

	/* 
	 * Set the search result
     */
	function updateResults(hits) {
		var list = $("<ol></ol>");
		for (var i = 0; i < hits.length; i++) {
			var url = hits[i];
			var elm = $("<li></li>").html($("<a></a>").attr("href", url).text(url)); 
			$(list).append(elm);
		}
		setResult(list);
	}

	/*
	 * Setup ajax request for search 
	 */
	function setupAjax(engine, term, success) {
		$.get("/index.php?action=search&engine=" + engine + "&term=" + term,
			  success).fail(showFailedAjax); 
	}

	function arrayUnique(arr) {
		return arr.reverse().filter(function (e, i, arr) {
    		return arr.indexOf(e, i+1) === -1;
		}).reverse();
	}

	/*
	 * Calculate the score (hit position) of 
	 * an url in match list arr
	 */
	function calcScore(arr, elm) {
		var ind = arr.indexOf(elm);
		if (ind == -1) ind = arr.length;
		return ind;
	}

	/*
	 * Convert the object list to a flat list
	 */
	function flattenList(list) {
		var keys = [ ];
		for (var key in list) {
			if (list.hasOwnProperty(key)) {
				keys = keys.concat([ key ]);
			}
		}

		keys = keys.sort(function(a,b) { return a-b;});

		var new_list = [ ];
		for (var i = 0; i < keys.length; i++) {
			var key = keys[i];
			var elm = list[key];
			var elm_lst = [].concat(elm);
			new_list = new_list.concat(elm_lst);
		}

		return new_list;
	}

	/*
	 * Merge the result from multiple search engines into a single
	 * hit list.
	 */
	function mergeResults(data1, data2, data3) {
		var all_results = arrayUnique(data1.concat(data2).concat(data3));
		var list = { };
		for (var i = 0; i < all_results.length; i++) {
			var url = all_results[i];
			var score = 0;

			score += calcScore(data1, url);
			score += calcScore(data2, url);
			score += calcScore(data3, url);

			if (list[score] != null) {
				list[score] = list[score].concat([ url ]);
			} else {
				list[score] = [ url ];
			}
		}

		/* Flatten the list and return the first 10 urls */
		return flattenList(list).slice(0,10);
	}

	/*
	 * Search all engines for the term 
	 */
	function searchAll(term, done) {
		setupAjax("Google", term, function (data1) {
			setupAjax("Bing", term, function (data2) {
				setupAjax("Yahoo", term, function (data3) {
					var res = mergeResults(data1, data2, data3);
					updateResults(res);
					done();
				});
			});
		});
	}

	/*
	 * Search a single engine for the term
	 */
	function searchEngine(engine, term, done) {
		setupAjax(engine, term, function (data) {
			updateResults(data);
			done();
		});
	}

	/*
	 * Contact the server and get the results for term with engine
	 */
	function doSearch(engine, term) {
		/* Default to Alla */
		if (engine == "") {
			engine = "Alla";
		}

		var throbber = showThrobber("#result");

		function done() {
			hideThrobber(throbber);
		};

		if (engine == "Alla") {
			searchAll(term, done);
		} else {
			searchEngine(engine, term, done);
		}

		updateTabSelection(engine);	
	}

	/* Standard clearing when focus in/out */
	function setupCleanFocusin(id) {
		var oldVal = $(id).val();
		$(id).on("click focusin", function() {
			if (this.value == oldVal) {
		    	this.value = "";
			}
		});

		$(id).on("focusout", function() {
		    if (this.value == "") {
		    	this.value = oldVal;
		    }
		});
	}

	function addCallbacks() {
		setupCleanFocusin("#searchtermfield");

		$(".tab").click(function() {
			var engine = $(this).text();
			var term = $("#searchtermfield").val();
			doSearch(engine, term);
		});
		$("#searchbutton").click(function() {
			var engine = window.location.hash.replace(/^#/, "");
			var term = $("#searchtermfield").val();
			doSearch(engine, term);
		});
		$("#searchtermfield").keyup(function (event) {
			if (event.keyCode == 13 && $("#searchtermfield").val() != "") {
				$("#searchbutton").click();	
			}
		});

		var engine = window.location.hash.replace(/^#/, "");
		updateTabSelection(engine);	
	}

	// Wait until page is fully loaded to set up hooks
	$(document).ready(addCallbacks);
})();
