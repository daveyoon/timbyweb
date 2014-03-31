<?php global $post; ?>

Latitude:
<input type="text" name="_lat" id="_latitude" value="<?php echo get_post_meta( $post->ID, '_lat', true); ?>" />
Longitude:
<input type="text" name="_lng" id="_longitude" value="<?php echo get_post_meta( $post->ID, '_lng', true); ?>" />
Type a location to navigate:
<input type="text" name="_location_address" id="_location_address" placeholder="e.g Monrovia" value="" />
<button type='button' onclick="getlocation();">Go</button>
<br />
<br />
Select the desired location below:
<div id="map-canvas" style="width:100%;height:300px;"></div>