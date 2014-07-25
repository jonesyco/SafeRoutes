// implement jquery events wherever possible 
// store data in html nodes instead of arrays -> NOT FEASABLE WITH GOOGLE
// reduce number of global vars (localize) 
// reduce number of checks whether object exist or not
// make code denser use better selectors
// switch mapping to jquery? -> FORGET ABOUT THAT
// localize static map layers

//namespace definition, singleton
var s2s = function(){
    
  google.load('visualization', '1');

  var marker_index = 0;
  var line_index = 0;
  var markers = [];
  var lines = [];
  var marker_active = null;
  var line_active = null;
  var marker_home = new google.maps.Marker();
  var marker_picked_school = new google.maps.Marker({"icon": null});
	var park_locations = new Array();
	var mapped_routes = new Array();
  var map = null;
  var trafficLayer = null;
  var school_id = 0;
	var school_name = null;
  var index = 0;
  var icons = ["", "img/walk.png", "img/road.png", "img/traffic_lights_green.png", "img/bike.png", "img/crash.png", "img/comment.png"];
  var c_type = null;
  var content = document.createElement('div');
  var acc_last_open = 0;
  var radiobutton_type = "actual"; // default setting of radio buttons
  var radiobutton_mode = "walking"; // default setting of radio buttons
  var drawingManager = null;
  var info = null;
  var i, dbid;
  var geocoder = new google.maps.Geocoder();
  var home_address = new google.maps.LatLng();
  var school_latlng = new google.maps.LatLng();
  var map_center = new google.maps.LatLng(); // ==> this value will be stored in order to recenter map after pop-up moved it
  var route_center = new google.maps.LatLng();
  var route_zoom;
	var routes_layer = null;
	
	
	var LayerObj = function(o) {
		// setting defaults and overwriting them on init using o
		//this.icon;
		this.zIndex = 10;
		this.map = null;
		this.show = true;
		this.id = 0;
		this.all = false;
		this.google_features = [];
		this.strokeColor = "#704aec";
		this.strokeOpacity = 1;
		this.strokeWeight = 5;
		this.resource = "dataproxies/maproutes.php";
		this.info_field = null;
		that = this
		$.each(o, function(key, value) {
			that[key] = value
		})
	}
	
	LayerObj.prototype.update = function(o) {
		var geometry_array = [];
		var google_feature;
		var path;
		var self = this;
		$.each(o, function(key, value) {
			self[key] = value
		})
		self = this
		//if (!this.all) { o.all = false; }
		if (this.show == true) {
			$.getJSON(self.resource + "?schoolid=" + this.id + "&all=" + this.all, function(json) {
				// currently duplicated for smother appearance TODO create function
				for (var m = 0; m < self.google_features.length; m++) {
					self.google_features[m].setMap(null);
				}
				self.google_features = [];
				for (var i = 0; i < json.routes.length; i++) {
					geometry_array=[]
					google_features = []
					// first step separating multi-geometry
					if (json.routes[i].Geometry.type.indexOf("Multi") == -1) {
						geometry_array.push(json.routes[i].Geometry);
					} else {
						for (var j=0; j < json.routes[i].Geometry.coordinates.length; j++) {
							var n = {
								"type": json.routes[i].Geometry.type.replace("Multi",""),
								"coordinates": json.routes[i].Geometry.coordinates[j]}
							//console.log(n);
							geometry_array.push(n);
						}
					}
					var that = self;	
					// second step transform into Google parts
					for (var k=0; k < geometry_array.length; k++) {
						if (json.routes[i].Geometry.type.indexOf("Line") != -1) {
							path = [];
							for (l=0; l < geometry_array[k].coordinates.length; l++) {
								path.push(new google.maps.LatLng(geometry_array[k].coordinates[l][1], geometry_array[k].coordinates[l][0]));
							}
							google_feature = new google.maps.Polyline({
								path: path,
								strokeColor: self.strokeColor,
								strokeOpacity: self.strokeOpacity,
								strokeWeight: self.strokeWeight,
								zIndex: self.zIndex
							})
							google_feature.setMap(that.map)
						}
						if (json.routes[i].Geometry.type.indexOf("Point") != -1) {
							pos = new google.maps.LatLng(geometry_array[k].coordinates[0][1], geometry_array[k].coordinates[0][0])
							google_feature = new google.maps.Marker({
								position: pos,
							})
							google_feature.setMap(that.map)
							if (self.info_field) {
								//	see http://stackoverflow.com/questions/3827409/google-maps-pass-information-into-an-event-listener
								google_feature.set("text", json.routes[i].Description);
								google_feature.set("backgroundColor", self.backgroundColor);
								google_feature.set("borderColor", self.borderColor );
								google.maps.event.addListener(google_feature, "mouseover", function(){
									// console.log(this.text);
									var info = new InfoBubble({
									backgroundColor: this.backgroundColor,
									borderColor: this.borderColor,
									maxWidth: 150,
									arrowSize: 10,
									arrowStyle: 2,
									padding: 10,
									disableAutoPan: true});
									info.setContent('<div class="ai">' + this.text + '</div>');
									info.open(that.map, this);
									google.maps.event.addListener(that.map, "click", function() {
									  info.close();
									});
									google.maps.event.addListener(this, "mouseout", function(){
										info.close()
									});
								})
							}
						}
						that.google_features.push(google_feature)
					}
				}
			})
		} else {
			for (var m = 0; m < self.google_features.length; m++) {
				self.google_features[m].setMap(null);
			}
		}
	}
	
	routeslayer = new LayerObj({});
	// defaults point to the properties of this layer
	
	walkingschoolbuslayer = new LayerObj({
		"zIndex": "20",
		"strokeColor": "#ff9933",
		"backgroundColor": '#ffcc99',
		"borderColor": '#ff9933',
		"resource": "dataproxies/mapwsb.php",
		"info_field": "descriptions",
		"show": false});
	
  
  var info = new InfoBubble({arrowSize: 10,
                        arrowStyle: 2,
                  			animation: false});

  var address_info = new InfoBubble({
                        backgroundColor: '#ffaa2a',
                        arrowSize: 10,
                        arrowStyle: 2,
                        padding: 10,
                  			disableAutoPan: true});

  var school_info = new InfoBubble({
                        backgroundColor: '#d9d0f2',
												borderColor: '#665495',
                        arrowSize: 10,
                        arrowStyle: 2,
                  			disableAutoPan: true});

	/* begin comment 
	var routes_layer = new google.maps.FusionTablesLayer({
    suppressInfoWindows: true,
      query: [{
        select: 'geometry',
        from: '1D0BA-z6VZwNZa00PQ9TnowFeTF0FjZ8iDQ9FyhI'
		,
		  	where: 'SchoolCode=0000'
      }] ,
			styles: [{
				polylineOptions: {
					strokeColor: "#704aec",
					strokeWeight: "5",
					strokeOpacity: "1"}
			}]
	}); */
 
 /*
	change_display = function(){
		routeslayer.update({"all": $(this).is(":checked")});
	} /*

	/* end comment */
  // move to initialize function?
  // map events
  google.maps.event.addListener(marker_home, "click", function() {
    // see http://stackoverflow.com/questions/5997070/google-maps-api-v3-infobubble-in-markerclusterer
    address_info.open();
  });
  google.maps.event.addListener(marker_picked_school, "mouseover", function() {
    school_info.open();
  });

  var zoom_to_extent = function(a, b) {						
    if (!isNaN(a.lat()) && !isNaN(a.lng()) && !isNaN(b.lat()) && !isNaN(b.lng())) {
      map.setOptions({zoom: 18});
      var lats = [a.lat(), b.lat()].sort();
      var lons = [a.lng(), b.lng()].sort();
      // this sort function sorts the coordinates alphabetically so -2.3 is bigger then -2.2
      // make this dynamic depending on the zoom level
      var sw_corner = new google.maps.LatLng(lats[0]-0.0005,lons[1]-0.002);
      var ne_corner = new google.maps.LatLng(lats[1]+0.002,lons[0]+0.002);
      var map_bounds = new google.maps.LatLngBounds(sw_corner, ne_corner);
       map.fitBounds(map_bounds);
    } else {
      //use those if no address available
      if (isNaN(a.lat()) && isNaN(a.lng()) && !isNaN(b.lat()) && !isNaN(b.lng())) {
        map.setCenter(b);
        map.setOptions({zoom: 16});
      }
      if (isNaN(b.lat()) && isNaN(b.lng()) && !isNaN(a.lat()) && !isNaN(a.lng())) {
        map.setCenter(a);
        map.setOptions({zoom: 16});
      }
    }
    route_center = map.getCenter();
    route_zoom = map.getZoom();
  }
  // border boundaries
  // from http://code.google.com/apis/maps/documentation/javascript/geocoding.html (add global variable geocoder)
  var codeAddress = function() {
    var address = document.getElementById("address").value;
    var sw = new google.maps.LatLng(38.51,-121.81);
    var ne = new google.maps.LatLng(38.58,-121.67);
    var bb = new google.maps.LatLngBounds(sw,ne);
    geocoder.geocode( {
                "address": address,
                // the bounds parameter provides a bias but not excludes addresses outside the bb
                "bounds": bb},
                function(results, status) {
                var addresstest = false;
                if (status === google.maps.GeocoderStatus.OK) {
                  for (i in results) {
                    if (bb.contains(results[i].geometry.location)) {  
                        addresstest = true;
                        break;
                      }
                    }
                    if (addresstest) {
                      home_address = results[i].geometry.location;
                      name = results[i].formatted_address.replace(/,/gi,"<br>").replace(/<br> USA/gi,"").replace(/<br> CA/gi, ", CA");
                      marker_home.setPosition(results[i].geometry.location);
                      marker_home.setMap(map);
                      address_info.setContent('<div class="ai">'+name+'<\/div>');
                      address_info.open(map, marker_home);
                    } else {alert("We could not find this location in or around the City of Davis.")}
                  } else { 
                    alert("We could not find this location in or around the City of Davis.")
                  }
      }
    );
    zoom_to_extent(home_address, school_latlng);			
  }

  var re_center_map = function() {if (map_center !== null) { map.panTo(map_center); }}

	var openpdf = function() {
		if (!school_id) {
			alert("Please pick a school first");
			first_panel();
		} else {
			file_name = school_name.replace(/ /g, "_");
			file_name = file_name.split(".").join("");
			file_name = "pdf/" + file_name.split("'").join("")+".pdf";
			// check whether file exist
			$.ajax({
				url: file_name,
        type:'HEAD',
        error: function() {
          alert("The file " + file_name + " is missing")
        },
				success: function(msg) {
					window.open(file_name, '_blank', "height=486, width=800");
				}
			});
		}
	}

  var close_instructions = function() {
    for (i = 1; i <= 6; i = i+1) {
      string = "#in"+i;
    	string2 = "#m"+i;
      $(string).hide();
    	$(string2).css("background-color", "#FFFFFF");
    }
  }

  var radiobuttons = function () {
    if (document.getElementById("radio1") !== null && document.getElementById("radio1").checked === true){radiobutton_type="actual";}
    if (document.getElementById("radio2") !== null && document.getElementById("radio2").checked === true){radiobutton_type="potential";}
    if (document.getElementById("radio3") !== null && document.getElementById("radio3").checked === true){radiobutton_mode="walking";}
    if (document.getElementById("radio4") !== null && document.getElementById("radio4").checked === true){radiobutton_mode="biking";}
    if (document.getElementById("radio5") !== null && document.getElementById("radio5").checked === true){radiobutton_mode="driving";}
  }

  var note = function (el) {
    c_type = el.delegateTarget.id.replace("m","");							
    remove_unfinished();
    close_instructions();
    string = "#in" + c_type;
    string2= "#m" + c_type;
    $(string2).css("background-color","#FFFFB6")
    if ($(string).is(":hidden")) { $(string).show(); }
      drawingManager.setMap(map);
      drawingManager.setOptions({
      markerOptions: {icon:icons[c_type], draggable:true}
    });
  }

  var add_route = function() {
    remove_unfinished();
    drawingManager.setMap(map);
  }

  // remove unfinished markers
  var remove_unfinished = function() {   
    if(typeof markers[marker_active] === 'object') {
          // checks whether marker has already a db id
        if (markers[marker_active].saved !== 1) {
            markers[marker_active].setMap(null);
            re_center_map();
        }
    }
    if(typeof lines[line_active] === 'object') {
        if (lines[line_active].saved !== 1) {
            lines[line_active].setMap(null);
            re_center_map();
        }
    }
    if (info.isOpen()) { re_center_map(); }
  }
  
  var pick_school = function() {
    if ($("#select_school option:selected").val()!=="0,0#0") {
      var aux = $("#select_school option:selected").val().split("#")
			var school_lng
			var school_lat
      school_name = aux[2];
      school_id = aux[1];
      aux1 = aux[0].split(",");
			school_latlng = new google.maps.LatLng(aux1[1], aux1[0]);
			marker_picked_school.setPosition(school_latlng);
			if (marker_picked_school.getMap() == null) { marker_picked_school.setMap(map); }		
          school_info.setContent('<div class="ai">'+school_name+'</div>');
          school_info.open(map, marker_picked_school);
					// keep testing
					routeslayer.update({"id": school_id, "all": $("#allschools").is(":checked"), "map": map})
					walkingschoolbuslayer.update({
						"id": school_id,
						"all": $("#allschools").is(":checked"),
						"show": $("#wsb").is(":checked"),
						"map": map});
					//routes_layer.setMap(map)
					//TODO: migrate to main layer class
					$.getJSON("dataproxies/mappark.php?schoolid="+school_id, function(json) {
						$.each(park_locations, function() {
							this.setMap(null)
						});
						park_locations = []
						$.each(json.parkloc,function(key,val) {
							var aux = val.geometry.split(",")
							var park_latlng = new google.maps.LatLng(aux[1], aux[0]);
							var parkmarker = new google.maps.Marker({"icon":"img/park.png"});
							parkmarker.setPosition(park_latlng);
							parkmarker.setMap(map);
							google.maps.event.addListener(parkmarker, "mouseover", function(){
																							var parkinfo = new InfoBubble({
																							backgroundColor: '#d9d0f2',
																							borderColor: '#665495',
																							maxWidth: 150,
																							arrowSize: 10,
																							arrowStyle: 2,
																							padding: 10,
																							disableAutoPan: true});
																							parkinfo.setContent($("#parkloctext").html());
																							parkinfo.open(map, this);
																							google.maps.event.addListener(map, "click", function() {
																								parkinfo.close();
																							});
																							google.maps.event.addListener(this, "mouseout", function(){
																								parkinfo.close()
																							})
																						});
							park_locations.push(parkmarker)
						})})
          find_panel();
			} else {
			  school_id = 0;
			  $("#schoolname").html("Please select your home and your school");
			}
			zoom_to_extent(school_latlng, home_address);
  }

  // could be simplified was created for two buttons
  var change_panel = function(a) {
      if (a === 0) { route_panel(); }
      if (a === 1) { marker_panel(); }
  }

	var first_panel = function() { $("#accordion1").accordion("activate",0); }
	var find_panel = function() { $("#accordion1").accordion("activate",1); }
  var route_panel = function() { $("#accordion1").accordion("activate",2); }
  var marker_panel = function() { $("#accordion1").accordion("activate",3); }
  var review_panel = function() { $("#accordion1").accordion("activate",4); }

  var submitter = function() {
      var datastring = 'email=' + $("#email_field").val();
      //$("#submit2").html('<p>Thank you for submitting comments for the Safe Routes to School Mapping Project - your input is invaluable to promoting safer walking and biking routes to schools in Solano County.<\/p>');
      //alert($("#email_field").val());
      $("#submit2").hide();
      $("#submit2b").show();
      
      $.ajax({
          type: "POST",
          url: "dataproxies/saveemail.php",
          data: datastring,
          success: function(data){
              alert ("Your data have been saved succesfully.");
          }
      });
  }

  // http://code.google.com/apis/maps/documentation/javascript/styling.html#creating_a_styledmapType
  // might be a better way to achieve the same ==> REPLACE
  // from http://code.google.com/apis/maps/documentation/javascript/examples/control-custom.html
  var TrafficControl = function(controlDiv, map) {
    // Set CSS styles for the DIV containing the control
    // Setting padding to 5 px will offset the control
    // from the edge of the map
      var controlUI = document.createElement('DIV'),
          controlText = document.createElement('DIV');
          controlDiv.style.padding = '5px';
          // Set CSS for the control border
    			controlUI.style.backgroundColor = 'white';
					controlUI.style.borderStyle = 'solid';
					controlUI.style.borderWidth = '1px';
					controlUI.style.cursor = 'pointer';
					controlUI.style.textAlign = 'center';
					controlUI.title = 'Click to toggle traffic';
					controlDiv.appendChild(controlUI);
					// Set CSS for the control interior
					// var controlText = document.createElement('DIV');
					controlText.style.fontFamily = 'Arial,sans-serif';
					controlText.style.fontSize = '13px';
					controlText.style.paddingLeft = '8px';
					controlText.style.paddingRight = '8px';
					
					controlText.innerHTML = 'Traffic';
					controlUI.appendChild(controlText);
		
					google.maps.event.addDomListener(controlUI, 'click', function() {
          if (trafficLayer.getMap() === map) {
              trafficLayer.setMap(null);
              controlText.style.fontWeight = "normal";
          } else {
              trafficLayer.setMap(map);
              controlText.style.fontWeight="bold";
          }
      });
  }

  var create_panels = function() {
    var myLayout = $('body').layout({
      west__size:	400,
      east__size:	300,
      // RESIZE Accordion widget when panes resize
      west__onresize:	$.layout.callbacks.resizePaneAccordions,
      east__onresize: $.layout.callbacks.resizePaneAccordions
    });
  
    // ACCORDION - in the West pane ==> see http://jqueryui.com/demos/accordion/
    $("#accordion1").accordion({
      fillSpace: true,
        resizable: true,
        collapsible : true, // needs some additional event handling in order to get the accordion and the functionality synched
      autoHeight: true,
      change: function(e,ui){
        var active = ($("#accordion1").accordion("option", "active"));
        switch(active) {
          case false: if (acc_last_open === 3){acc_last_open = 2;}
            $("#accordion1" ).accordion({active: acc_last_open+1});
          break;  
          case 0: drawingManager.setMap(null);
            map.setOptions({
            mapTypeId: google.maps.MapTypeId.HYBRID
          });
          close_instructions()
          break;
					case 1:
						drawingManager.setMap(null);
					break;
					case 2: // line
          drawingManager.setOptions({
            drawingMode: google.maps.drawing.OverlayType.POLYLINE
          });
					drawingManager.setMap(map);
          map.setOptions({
            mapTypeId: google.maps.MapTypeId.HYBRID
          });
          remove_unfinished();
            close_instructions()
          break;
          case 3: // marker
            drawingManager.setOptions({
							drawingMode: google.maps.drawing.OverlayType.MARKER
            });
            map.setOptions({
              mapTypeId: google.maps.MapTypeId.HYBRID
            });
            remove_unfinished();
            close_instructions()
          break;
          case 4: drawingManager.setMap(null);
            map.setOptions({
							zoom: route_zoom,
							center: route_center,
							mapTypeId: google.maps.MapTypeId.HYBRID
            });
            close_instructions()
          break;
        }
        acc_last_open = active;
    }
  });
  }

  var create_info = function(marker_id) {
      content.innerHTML = $("#info_template_" + [markers[marker_id].type]).html();
      info.setContent(content);
      info.open(map, markers[marker_id]);
      for (i=11; i<=65; i=i+1) {
          if (typeof markers[marker_id].v[i] !== 'undefined') {
              if (markers[marker_id].v[i] !== null) {
              // checks whether field exists
                  if (typeof document.getElementById("v"+i) !== "undefined") {document.getElementById("v"+i).value = markers[marker_id].v[i];}
              }
          }
      }
      if (markers[marker_id].dbid !== null) {document.getElementById("dbid").value = markers[marker_id].dbid;}
      if (typeof markers[marker_id].user_comment !== "undefined") {document.getElementById("comment_here").value = markers[marker_id].user_comment;}
      info.setContent(content);
  }
  
  var create_info_line = function(line_id, lat, lon) {
      content.innerHTML=$("#infoline_template").html()
      info.setContent(content);
      var pos = lines[line_id].getPath().getAt(lines[line_id].getPath().length-1);
      var pos_mark = new google.maps.Marker({
          position: pos
      });
      //alert(lat+"\n"+lon);
      if (typeof lat !== "undefined" && typeof lon !== "undefined") {
    latlng = new google.maps.LatLng(lat, lon);
    info.setPosition(latlng);
    info.open(map);
      } else {
    info.open(map, pos_mark);
      }
      if (document.getElementById("v100") !== null){document.getElementById("v100").value = lines[line_id].v100;}
      if (lines[line_id].type === "actual"){document.getElementById("radio1").checked=true;}
      if (lines[line_id].type === "potential"){document.getElementById("radio2").checked=true;}
      if (lines[line_id].mode === "walking"){document.getElementById("radio3").checked=true;}
      if (lines[line_id].mode === "biking"){document.getElementById("radio4").checked=true;}
      if (lines[line_id].dbid !== null) {document.getElementById("dbid").value = lines[line_id].dbid;}
      if (typeof lines[line_id].user_comment !== "undefined") { document.getElementById("comment_here_2").value = lines[line_id].user_comment; }
      info.setContent(content); 
  }

  // this function is called, when popup is open to recenter the marker after close, if savemarker is called from dragend map will be not recentered
  this.save_recenter = function() {
    re_center_map();
    savemarker()
  }

  var savemarker = function() {
      //re_center_map();
      user_comment = document.getElementById('comment_here').value;
      dbid = markers[marker_active].dbid;
      position = markers[marker_active].getPosition();
      position_text = markers[marker_active].getPosition().lat()+ ", " + markers[marker_active].getPosition().lng();
      markers[marker_active].user_comment = user_comment;
      var snippet="";
      for (i = 11; i <= 65; i = i+1) {
          if (document.getElementById('v'+i) !== null) {
              snippet = snippet + "&v"+i+"="+document.getElementById('v'+i).value;
              markers[marker_active].v[i] = document.getElementById('v'+i).value;
          }
      }
      var datastring = "coordinates="+position_text+snippet+"&comment="+user_comment+"&school="+school_id+"&school_name="+school_name+"&type="+c_type;
      if (typeof lines[line_active] === 'object') {
          datastring = datastring+"&route="+lines[line_active].dbid;
      }
      if (dbid !== null) { datastring = datastring +"&dbid="+dbid; }
      $.ajax({
          //async: false, // this option disables the asynchronous operation
    type: "POST",
          url: "dataproxies/savepoints.php",
          data: datastring,
    // beforeSend: function(){$("#dialog").dialog();},
    // this callback function is added to check whether save is triggered
    beforeSend: function() { markers[marker_active].saved = 1 },
          success: function(data) {
        //$("#dialog").dialog("close")		
        if (markers[marker_active].dbid === null) {
             markers[marker_active].dbid = data;
             }
          },
          response: function() {} // create response function checking whether data saved
      });
      info.close();
      close_instructions();
  }

  this.saveline = function() {
      dbid = lines[line_active].dbid;
      var kml, y, x, datastring;
      re_center_map();
      radiobuttons();
      if (lines[line_active].user_comment !== null) { lines[line_active].user_comment = document.getElementById('comment_here_2').value; }
      if (lines[line_active].v100 !== null) { lines[line_active].v100 = document.getElementById('v100').value; }
      if (lines[line_active].mode !== null) { lines[line_active].mode = radiobutton_mode; }
      if (lines[line_active].type !== null) { lines[line_active].type = radiobutton_type; }
      kml="<LineString><coordinates>";
      for (i = 0; i < lines[line_active].getPath().getLength(); i = i+1) {
          y = lines[line_active].getPath().getAt(i).lat();
          x = lines[line_active].getPath().getAt(i).lng();
          kml = kml+x+","+y+" ";
      }
      kml = kml + "</coordinates></LineString>";
      
      datastring = "coordinates="+kml+"&comment="+lines[line_active].user_comment+"&school="+school_id+"&school_name="+school_name+"&v100="+lines[line_active].v100+"&type="+lines[line_active].type+"&mode="+lines[line_active].mode;
			if (dbid !== null) {
          datastring = datastring + "&dbid="+ dbid;
      }
              
      $.ajax({
          type: "POST",
          url: "dataproxies/savelines.php",
          data: datastring,
    // this callback function is added to check whether save is triggered
    beforeSend: function() { lines[line_active].saved = 1; },
          success: function(data) {
              if (typeof lines[line_active] !== "undefined" && lines[line_active].dbid === null) {
                  lines[line_active].dbid = data;
                  //alert(lines[line_active].dbid);
              }
          }
      });
      info.close();
  }

  this.removemarker = function() {
      var datastring;
      markers[marker_active].setMap(null);
      if(typeof markers[marker_active] === 'object') {
          if (markers[marker_active].dbid != null) {
              datastring = "dbid="+markers[marker_active].dbid+"&delete=1";
              $.ajax({
                  type: "POST",
                  url: "dataproxies/savepoints.php",
                  data: datastring,
                  success: function(data) {},     
                  response: function() { alert(data); } // create response function checking whether data saved
              });
          }    
      }
      markers[marker_active] = null;
      marker_active = null;
      re_center_map();
      info.close();
  }

  this.removeline = function() {
      lines[line_active].setMap(null);
      if(typeof lines[line_active] === 'object') {
          if (lines[line_active].dbid != null) {
              datastring = "dbid="+lines[line_active].dbid+"&delete=1";
              $.ajax({
                  type: "POST",
                  url: "dataproxies/savelines.php",
                  data: datastring,
                  success: function(data) {},     
                  response: function() { alert(data); } // create response function checking whether data saved
              });
          }    
      }
      line_active = null;
      re_center_map();
      info.close();    
  }

  var resize_map_to_container = function() {
      $("#map_canvas").css("height","100%");
      $("#map_canvas").css("width","100%");
      // event trigger fits map to container
      google.maps.event.trigger(map, "resize");
  }
  
  // simplify move functions out
  this.initialize = function() {
      
      var myLayout = $('body').layout({
              onclose_end: resize_map_to_container,
              onopen_end: resize_map_to_container
      });
      myLayout.sizePane("west", 270);
    
      var latlng = new google.maps.LatLng(38.555, -121.745);
      route_center = latlng;
      route_zoom = 10;
      
      var myOptions = {
          zoom: route_zoom,
          center: route_center,
          mapTypeId: google.maps.MapTypeId.HYBRID,
          scaleControl: true
      };
      
      //map = $("#map_canvas").gmap();
      map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
			
      trafficLayer = new google.maps.TrafficLayer();
      
      // http://code.google.com/apis/maps/documentation/javascript/examples/control-custom.html
      var homeControlDiv = document.createElement('DIV');
      var homeControl = new TrafficControl(homeControlDiv, map);
      homeControlDiv.index = 1;
      map.controls[google.maps.ControlPosition.TOP_RIGHT].push(homeControlDiv);
      // end copy
      
      // applies jquery layout
      create_panels();
      
      // get school data
      $.getJSON("dataproxies/mapschools_new.php", function(json) {
        var schools = [];
        $.each(json.schools,function(key,val) {
          $('#select_school')
						.append('<option '+' value="'+val.geometry+'#'+val.OBJECTID+'#'+val.Name+'">'+val.Name+'</option>')	
        });
				$('#select_school').children().addClass("s2s-missing");
      });
             
      drawingManager = new google.maps.drawing.DrawingManager({
          drawingControl: false,
          drawingControlOptions: {
              position: google.maps.ControlPosition.TOP_RIGHT,
              drawingModes: [
                  google.maps.drawing.OverlayType.MARKER,
                  google.maps.drawing.OverlayType.POLYLINE,]
          },
          markerOptions: {
              icon: new google.maps.MarkerImage('img/walk.png'),
        draggable: true
          },
          polylineOptions: {
              fillOpacity: 1,
              strokeWeight: 2,
              strokeColor: "#f01b84",
              clickable: true,
              zIndex: 1,
              editable: true
          }
      });
      
      // action after new marker was created, marker will be removed if not saved
      google.maps.event.addListener(drawingManager, 'markercomplete', function(marker) { 
          map_center = map.getCenter(); //store for reset after infowindow closed
          drawingManager.setMap(null); // needs to go somewhere else!!!
          marker_index = marker_index + 1;
          marker_active = marker_index;
          marker.dbid = null;
          marker.type = c_type;
          markers[marker_active] = marker;
          markers[marker_active].v = new Array() // saves user variables
          create_info(marker_active);
                    
          google.maps.event.addListener(markers[marker_active], "click", function(){
              marker_active = jQuery.inArray(this, markers);
							create_info(marker_active);
          })
          
          google.maps.event.addListener(markers[marker_active], "drag", function() { info.close(); })
          
          google.maps.event.addListener(markers[marker_active], "dragend", function() {
              marker_active = jQuery.inArray(this, markers);
              map_center = map.getCenter(); // set new map center when dragged
              savemarker();
          })
      })
      
      google.maps.event.addListener(info, 'closeclick', function() { remove_unfinished(); })
      
      google.maps.event.addListener(drawingManager, 'polylinecomplete', function(polyline) { 
          drawingManager.setMap(null);
          map_center = map.getCenter(); //store for reset after infowindow closed
          line_index = line_index + 1;
          line_active = line_index;
          polyline.dbid = null;
          lines[line_active] = polyline;
          create_info_line(line_active);
          
          // to make sure that the infowindow pops up on the closest marker
    // http://stackoverflow.com/questions/4057665/google-maps-api-v3-find-nearest-markers
    google.maps.event.addListener(lines[line_active], "click", function(event){
              var lat = event.latLng.lat();
        var lng = event.latLng.lng();
        line_active = jQuery.inArray(this, lines);
              create_info_line(line_active, lat, lng);
          })
          
    // to make sure that the infowindow pops up on the closest marker
    // http://stackoverflow.com/questions/4057665/google-maps-api-v3-find-nearest-markers
    // review this part, there might be a little bit too much going on here
    google.maps.event.addListener(lines[line_active], "capturing_changed", function(){
          line_active = jQuery.inArray(this, lines);
          s2s.saveline();
          })
     })
  
    // close infowindow when clicked in another place
    $("#menus").click( function() {
      if(typeof info === 'object') {
                      info.close();
                      remove_unfinished();
      }
    }); 
		
		// TODO migrate to layerObj
		var geojson_load = function() {
			$.ajax({
				url: "staticlayers/DavisSchools.json",
				type:'POST',
				error: function() {
					alert("The school layer is missing! Please contact the system administrator.")
				},
				success: function(json) {
					$.each(json.features, function() {
						if (this.geometry.type === "Polygon" || this.geometry.type === "MultiPolygon") {
							feature = new Array;
							index = -1;
							$.each(this.geometry.coordinates, function(){
								index = index + 1;
								path = new Array;
								$.each(this, function(){
									path.push(new google.maps.LatLng(this[1], this[0]));
								})
								feature[index] = new google.maps.Polygon({
									paths: path,
									strokeColor: "#704aec",
									strokeOpacity: 1,
									strokeWeight: 1,
									fillColor: "#704aec",
									fillOpacity: 0.35
								})
								feature[index].setMap(map);
							})
						}
					}) 
				}
			})
		}
		geojson_load();
		
    $(window).resize( function() {
      $("#map_canvas").css("height","100%").css("width","100%");
      google.maps.event.trigger(map, "resize");
      $("#menus").css("height", "auto");
      $("#accordion1").accordion("resize");
         if (home_address !== null || school_latlng !== null) { zoom_to_extent(home_address, school_latlng); }
      });
  
      // html events
      $("#select_school").change( pick_school );
      $("#another").click( add_route );
      $("#done1").click( marker_panel );
      $("#submit_address_button").click( codeAddress );
      $(".done2").click( review_panel );
      $("#submit11").click( submitter );
      $(".marker_menu").click( $(this), note );
			// empty links do not do anything (overwrite default behavior with false)
			$("a.emptylink").click( function() {return false;} )
			$("#routebutton").click( route_panel );
			$("#commentbutton").click( marker_panel );
			$("#pdfmap").click( openpdf );
			$("#allschools").change( function(){
				routeslayer.update({"all": $(this).is(":checked")});
				// walkingschoolbuslayer.update({"all": $(this).is(":checked")});
			});
			$("#wsb").change( function(){walkingschoolbuslayer.update({"show": $(this).is(":checked")})} );
			$("#wsb2").change( function(){walkingschoolbuslayer.update({"all": $(this).is(":checked")})} );
  }
}

// initialize a singleton and overwrite class definition
s2s = new s2s() 
$(s2s.initialize())
		