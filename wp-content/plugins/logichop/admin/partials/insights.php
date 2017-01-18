<?php
	
	if (!defined('ABSPATH')) { header('location: /'); die; }
	
	$page_data = array();
	$page_labels = array();
	$page_colors = array();
	$page_color = [2,124,182];
	$page_label = true;
	
	$goal_data = array();
	$goal_labels = array();
	$goal_colors = array();
	$goal_color = [0,196,96]; 
	$goal_label = true;
	
	$views = array (
					'u30' 	=> __('Unique Data: Past 30 Days', 'logichop'),
					'u7' 	=> __('Unique Data: Past 7 Days', 'logichop'),
					'u1' 	=> __('Unique Data: Since Yesterday', 'logichop'),
					'u' 	=> __('Unique Data: Today', 'logichop'),
					'a30' 	=> __('Aggregate Data: Past 30 Days', 'logichop'),
					'a7' 	=> __('Aggregate Data: Past 7 Days', 'logichop'),
					'a1' 	=> __('Aggregate Data: Since Yesterday', 'logichop'),
					'a' 	=> __('Aggregate Data: Today', 'logichop')
				);
	
	$view = 'u30';
	if (isset($_GET['view'])) {
		if (array_key_exists($_GET['view'], $views)) {
			$view = $_GET['view'];
		}
	}
	
	$goals = json_decode($this->logic->goals_get_json(), true);
	$pages = json_decode($this->logic->pages_get_json(), true);
	$pages[0] 	= __('Frontpage Posts', 'logichop');
	$pages[-1] 	= __('404 Error', 'logichop');
	
	$insights = $this->logic->api_post('insights', array('view' => $view));
	if (!isset($insights['Pages'])) $insights['Pages'] = array();
	if (!isset($insights['Goals'])) $insights['Goals'] = array();
?>

<div class="wrap logichop-insights">

	<h1><?php _e('Insights', 'logichop'); ?></h1>
	
	<div class="row">
		<div class="col-xs-12">
			<select id="logichop-view" class="form-control input-lg">
				<?php
					foreach ($views as $key => $value) {
						printf('<option value="%s" %s>%s</option>',
									$key,
									($view == $key) ? 'selected' : '',
									$value
								);
					}
				?>
			</select>
		</div>
	</div>	
		
	<div class="row charts">
		<div class="col-sm-2"></div>
		<div class="col-sm-4">
			<canvas class="chart" id="goals" width="400" height="400" data-title="<?php _e('Goals Triggered', 'logichop'); ?>"></canvas>
		</div>
		<div class="col-sm-4">
			<canvas class="chart" id="pages" width="400" height="400" data-title="<?php _e('Pages Viewed', 'logichop'); ?>"></canvas>
		</div>
		<div class="col-sm-2"></div>
		<div class="legend text-right">
			<small><?php echo $views[$view]; ?></small>
		</div>
	</div>
	
	<div class="row">
		<div class="col-xs-12">
			<table class="table table-striped table-bordered table-condensed">
  				<tr style="background: <?php printf('rgba(%s,%s,%s,1);', $goal_color[0], $goal_color[1], $goal_color[2]); ?>">
  					<th><?php _e('Goals Triggered', 'logichop'); ?></th>
  					<th><?php _e('Goal Count', 'logichop'); ?></th>
  				</tr>
  				<?php
  					if ($pages && $insights['Goals']) {
						foreach ($insights['Goals'] as $id => $total) {
							if (array_key_exists($id, $goals)) {
								$goal_data[] = $total;
								$goal_labels[] = $goals[$id];
								printf('<tr>
											<td><a href="/wp-admin/post.php?post=%s&action=edit">%s</a></td>
											<td>%s</td>
										</tr>',
										$id,
										$goals[$id],
										$total
									);
							}
						}
						if (count($goal_data) > 0) {
							for ($i = 1; $i <= count($goal_data); $i++) {
							 	$goal_colors[] = sprintf('rgba(%s,%s,%s,%s)',
															$goal_color[0],
															$goal_color[1],
															$goal_color[2],
															(1 / $i)
														);
							}
						}
  					} else {
  						$goal_data[] = '1';
  						$goal_label = false;
						$goal_labels[] = __('No Goal Views', 'logichop');
						printf('<tr>
  									<td colspan="2">%s</td>
  								</tr>',
  								__('No Goals Viewed', 'logichop')
  							);
  					}		
  				?>
			</table>
		</div>
	</div>
	
	<div class="row">
		<div class="col-xs-12">
			<table class="table table-striped table-bordered table-condensed">
  				<tr style="background: <?php printf('rgba(%s,%s,%s,1);', $page_color[0], $page_color[1], $page_color[2]); ?>;">
  					<th><?php _e('Pages Viewed', 'logichop'); ?></th>
  					<th><?php _e('Page Count', 'logichop'); ?></th>
  				</tr>
  				<?php
  					if ($pages && $insights['Pages']) {
						foreach ($insights['Pages'] as $id => $total) {
							if (array_key_exists($id, $pages)) {
								$page_data[] = $total;
								$page_labels[] = $pages[$id];
								printf('<tr>
											<td><a href="/wp-admin/post.php?post=%s&action=edit">%s</a></td>
											<td>%s</td>
										</tr>',
										$id,
										$pages[$id],
										$total
									);
							}
						}
						if (count($page_data) > 0) {
							for ($i = 1; $i <= count($page_data); $i++) {
							 	$page_colors[] = sprintf('rgba(%s,%s,%s,%s)',
															$page_color[0],
															$page_color[1],
															$page_color[2],
															(1 / $i)
														);
							}
						}
  					} else {
  						$page_data[] = '1';
  						$page_label = false;
						$page_labels[] = __('No Page Views', 'logichop');
						printf('<tr>
  									<td colspan="2">%s</td>
  								</tr>',
  								__('No Pages Viewed', 'logichop')
  							);
  					}	
  				?>
			</table>
		</div>
	</div>
	
	<script>
		var pages = chart_donut(
						jQuery('#pages'), 
						<?php echo json_encode($page_labels); ?>, 
						<?php echo json_encode($page_data); ?>,
						<?php echo json_encode($page_colors); ?>,
						<?php echo json_encode($page_label); ?>
					);
		var goals = chart_donut(
						jQuery('#goals'), 
						<?php echo json_encode($goal_labels); ?>, 
						<?php echo json_encode($goal_data); ?>,
						<?php echo json_encode($goal_colors); ?>,
						<?php echo json_encode($goal_label); ?>
					);
		
		function chart_donut (element, labels, data, bgcolors, tooltips) {
			var data = {
					labels: labels,
					datasets: [
						{
							data: data,
							backgroundColor: bgcolors,
							borderWidth: .5,
							borderColor: 'rgba(255,255,255,1)'
						}]
				};
			return new Chart(element, {
							type: 'doughnut',
							data: data,
							options: {
								title: {
									display: true,
									text: element.attr('data-title'),
									position: 'top',
									fontSize: 16,
									fontStyle: 'normal',
									fontColor: 'rgba(0,0,0,.9)'
								},
								tooltips: {
									enabled: tooltips
								},
								legend: {
									display: false,
									position: 'bottom'
								},
								cutoutPercentage: 65
							}
						});
		}
		
		jQuery('#logichop-view').on('change', function () {
			window.location.href = window.location.href + '&view=' + jQuery(this).val();
		});
	</script>
	
</div>







