<?php
/*
* @Author 		Jaed Mosharraf
* Copyright: 	2015 Jaed Mosharraf
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



global $wpdb;

$TABLE_NAME	= $wpdb->prefix . WPPN_DATA_TABLE;
$PER_PAGE 	= get_option('wppn_per_page', 20);
$paged 		= isset( $_GET['paged'] ) ? sanitize_text_field( $_GET['paged'] ) : 1;
$counter 	= 0;
$OFFSET 	= ($paged - 1) * $PER_PAGE;
$wdm_query 	= "SELECT * FROM $TABLE_NAME ORDER BY id DESC LIMIT $PER_PAGE OFFSET $OFFSET";

$wppn_subscribers = $wpdb->get_results( $wdm_query, OBJECT );
if( empty( $wppn_subscribers ) ) $wppn_subscribers = array();

// echo "<pre>"; print_r( $wppn_subscribers ); echo "</pre>";

?>

<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div>
	<h2>WP Push Notifications - Subscribers List</h2><br><br>
	
	<table class="wp-list-table widefat fixed striped posts wppn_subscribers_list">
        <thead>
        <tr>
            <th width="20" id="" class="serial column-serial" scope="col"> </th>
            <th id="" class="" scope="col">Token</th>
            <th width="720"id="" class="" scope="col">Subscription Details</th>
            <th width="120" id="" class="" scope="col">User & Date-Time</th>
        </tr>
        </thead>


        <tbody id="the-list">
        
		<?php $count = 0; foreach( $wppn_subscribers as $sub_record ): $count++; ?>

		<tr id="" class="">  
            <td class="" data-colname="thumb column-serial"><?php echo $count; ?></td>
            <td class="" data-colname="Token">
				<strong class="s_token"><?php echo $sub_record->s_token; ?></strong>
                <div class="row-actions">
					<span class="s_id">Record ID: <?php echo $sub_record->id; ?></span> | 
					<span class="s_delete" s_id="<?php echo $sub_record->id; ?>">Delete</span>
				</div>
			</td>
			<td class="" data-colname="Subscription Details">
				<span class="s_endpoint"><?php echo $sub_record->s_endpoint; ?></span> 
				<div class="row-actions">
					<span class="s_key"><?php echo $sub_record->s_key; ?></span>
				</div>
			</td>
			<td class="" data-colname="User">
				
				<?php if( !empty( $sub_record->s_user_id ) ): ?>
				<?php $userdata = get_userdata( $sub_record->s_user_id ); ?>
				<a class="s_user_id" href="user-edit.php?user_id=<?php echo $sub_record->s_user_id; ?>" target="_blank">
					<?php echo $userdata->display_name;?>
				</a>
				<?php else: ?>
				<span class="wppn_no_data"><?php echo __('No Data',WPPN_TEXTDOMAIN); ?></span>
				<?php endif; ?>
				
				<br>
				
				<?php if( !empty( $sub_record->s_datetime ) ): ?>
				<?php $date = date_create($sub_record->s_datetime); ?>
				<?php //$time_ago = human_time_diff( date("U", strtotime( $sub_record->datetime )), date("U", strtotime( current_time('mysql') )) ); ?>
				<span class="s_datetime"><?php echo date_format($date,"M d, Y H:i:s"); //echo $sub_record->datetime; ?></span>
				<?php else: ?> <span class="wppn_no_data"><?php echo __('No Data',WPPN_TEXTDOMAIN); ?></span>
                <?php endif; ?>
				
			</td>
		</tr>
		
		<?php endforeach; ?>

        </tbody>

        <tfoot>
        <tr>
            <th width="20" id="" class="serial column-serial" scope="col"> </th>
            <th id="" class="" scope="col">Token</th>
            <th width="720"id="" class="" scope="col">Subscription Details</th>
            <th width="120" id="" class="" scope="col">User & Date-Time</th>
        </tr>
        </tfoot>

    </table>
	
	
	<?php 
	$num_rows_query = $wpdb->get_results("SELECT * FROM $TABLE_NAME ORDER BY id DESC");
	$big = 999999999;
	$paginate_links = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, $paged ),
		'total' => (int)ceil($wpdb->num_rows / $PER_PAGE)
	) );
	?>
	<div class="tablenav bottom">
		<div class="tablenav-pages"><span class="pagination-links"><?php echo $paginate_links;?></div>
		<br class="clear">
	</div>
</div>
