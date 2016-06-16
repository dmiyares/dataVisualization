<?php


// ----------------------------------------------------------------------------
//
//  File: bubble-chart.php
//  Creation Date:  06/10/16 12:12:23
//  Last Modified:  06/16/16 16:14:54
//  Purpose: display a bubble chart of threads on vBulletin forums.
//					 Uses render code directly from http://bl.ocks.org/mbostock/4063269
//
//					 Load to root directy of forum & should be good to go.
//
// ----------------------------------------------------------------------------
 
	
 		include("includes/config.php");
		
		


#DATABASE 
 DEFINE ("DATABASE_NAME",$config['Database']['dbname']);
 DEFINE ("DATABASE_USER",$config['MasterServer']['username']);
 DEFINE ("DATABASE_PASSWORD",$config['MasterServer']['password'] );
 DEFINE ("DATABASE_HOST",$config['MasterServer']['servername']);
 DEFINE ("DATABASE_PREFIX",$config['Database']['tableprefix']);


// SET VARIABLES TO INITIAL STATE

	$ForumBubbles=array('name' => 'forumbubbles');
  $counter=0;
  
  $SubForum=array();
  $TopForum=array();
  $tree=array();
  
  $link=connect_to_database();
  

// STEP 1 GET ALL THE PARENTLESS FORUMS

	$MasterTree= build_child_tree($link,-1,$counter);

				#		print_r($MasterTree);
				
				
				$BubbleArray=array('name' => 'forums','children' => $MasterTree);
						
// STEP X GENERATE JSON FILE FOR BUBBLE CHART.

#print_r($BubbleArray);

$json_data=json_encode($BubbleArray);

$myfile = fopen("forums.json", "w") or die("Unable to open file!");

fwrite($myfile, $json_data);

#exit();

?><!DOCTYPE html>
<meta charset="utf-8">
<style>

text {
  font: 10px sans-serif;
}

</style>
<body>
<script src="//d3js.org/d3.v3.min.js"></script>
<script>

var diameter = 960,
    format = d3.format(",d"),
    color = d3.scale.category20c();

var bubble = d3.layout.pack()
    .sort(null)
    .size([diameter, diameter])
    .padding(1.5);

var svg = d3.select("body").append("svg")
    .attr("width", diameter)
    .attr("height", diameter)
    .attr("class", "bubble");

d3.json("forums.json", function(error, root) {
  if (error) throw error;

  var node = svg.selectAll(".node")
      .data(bubble.nodes(classes(root))
      .filter(function(d) { return !d.children; }))
    .enter().append("g")
      .attr("class", "node")
      .attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

  node.append("title")
      .text(function(d) { return d.className + ": " + format(d.value); });

  node.append("circle")
      .attr("r", function(d) { return d.r; })
      .style("fill", function(d) { return color(d.packageName); });

  node.append("text")
      .attr("dy", ".3em")
      .style("text-anchor", "middle")
      .text(function(d) { return d.className.substring(0, d.r / 3); });
});

// Returns a flattened hierarchy containing all leaf nodes under the root.
function classes(root) {
  var classes = [];

  function recurse(name, node) {
    if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
    else classes.push({packageName: name, className: node.name, value: node.size});
  }

  recurse(null, root);
  return {children: classes};
}

d3.select(self.frameElement).style("height", diameter + "px");

</script>

<?php

########################################################################################################################

function CONNECT_TO_DATABASE()
 {
       
$link = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD) 
 or die("Unable to connect to MySQL");

mysqli_select_db($link,DATABASE_NAME);
mysqli_query($link,"SET CHARACTER SET utf8");
mysqli_query($link,"SET NAMES 'utf8'");  

 return($link); 

}
########################################################################################################################

function build_child_tree($link,$ForumID,$counter=0,$tree=array()){
	
					
									$SubTree=array();
 								
 									$counter++;
				
#				print($counter);
#				if($counter>10){die("NOT WORKING");	}

									$sql="select forumid as SubForumID,(SELECT count(*) from ".DATABASE_PREFIX."thread where forumid=SubForumID) as Size from ".DATABASE_PREFIX."forum where parentid ='".$ForumID."'";

 
									$result= mysqli_query($link,$sql)  or die("<HR><pre><b>cannot execute sql:</b>".$sql."</pre><HR>-->".mysqli_error($link));       
				
									$num_children = mysqli_num_rows($result) -2;
									
									if($num_children<0){$num_children=0;}
	 		
									
										while($row = mysqli_fetch_assoc($result)) {
											$SubForumID=$row['SubForumID'];
											$Size=$row['Size'];
											$name_of_subforum=pull_master_forum_name($link,$SubForumID);

 
if($Size==0){
												$SubTree[]=array('name' => $name_of_subforum,'children' => build_child_tree($link,$SubForumID,$counter,$tree));								 
						} else {
												$SubTree[]=array('name' => $name_of_subforum,'size' => $Size);								 
}																			
												
 
							 				if($num_children >0){
							 					build_child_tree($link,$SubForumID,$counter,$SubTree);
							 				} # end other stuff
	


										}  # end while
				 
				 			
						return($SubTree);
}
########################################################################################################################

function pull_master_forum_name($link,$ForumID){
	
	 
	$sql="select title from ".DATABASE_PREFIX."forum where forumid=".$ForumID;

			$result= mysqli_query($link,$sql)  or die("<HR><pre><b>cannot execute sql:</b>".$sql."</pre><HR>-->".mysqli_error($link));       
							$row = mysqli_fetch_assoc($result);


				$name=$row['title'];

	return($name);
		
	
}  		