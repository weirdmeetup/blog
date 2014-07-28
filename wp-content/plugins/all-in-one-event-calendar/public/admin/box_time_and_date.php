<div class="ai1ec-panel-heading">
	<a data-toggle="ai1ec-collapse"
		data-parent="#ai1ec-add-new-event-accordion"
		href="#ai1ec-time-and-date-box">
		<i class="ai1ec-fa ai1ec-fa-clock-o ai1ec-fa-fw"></i>
		<?php _e( 'Event date and time', AI1EC_PLUGIN_NAME ); ?>
	</a>
</div>
<div id="ai1ec-time-and-date-box"
	class="ai1ec-panel-collapse ai1ec-collapse ai1ec-in">
	<div class="ai1ec-panel-body">
		<?php wp_nonce_field( 'ai1ec', AI1EC_POST_TYPE ); ?>
		<?php if ( $instance_id ) : ?>
			<input type="hidden"
				name="ai1ec_instance_id"
				id="ai1ec_instance-id"
				value="<?php echo $instance_id; ?>">
		<?php endif; ?>
		<table class="ai1ec-form">
			<tbody>
				<tr>
					<td colspan="2">
						<label for="ai1ec_all_day_event">
							<input type="checkbox" name="ai1ec_all_day_event"
								id="ai1ec_all_day_event" value="1" <?php echo $all_day_event; ?>>
							<?php _e( 'All-day event', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<label for="ai1ec_instant_event">
							<input type="checkbox" name="ai1ec_instant_event"
								id="ai1ec_instant_event" value="1" <?php echo $instant_event; ?>>
							<?php _e( 'No end time', AI1EC_PLUGIN_NAME ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<td class="ai1ec-first">
						<label for="ai1ec_start-date-input">
							<?php _e( 'Start date / time', AI1EC_PLUGIN_NAME ); ?>:
						</label>
					</td>
					<td>
						<input type="text" class="ai1ec-date-input ai1ec-form-control"
							id="ai1ec_start-date-input">
						<input type="text" class="ai1ec-time-input ai1ec-form-control"
							id="ai1ec_start-time-input">
						<?php if ( $timezone_string ) : ?>
							<small>
								<?php printf(
									__( '(Time zone: %s)', AI1EC_PLUGIN_NAME ),
									'<abbr title="' . $timezone .
										'" class="ai1ec-tooltip-toggle">' . $timezone_string .
										'</abbr>'
								); ?>
							</small>
						<?php endif; ?>
						<input type="hidden"
							name="ai1ec_start_time"
							id="ai1ec_start-time"
							value="<?php echo $start->format_to_javascript(); ?>">
					</td>
				</tr>
				<tr>
					<td>
						<label for="ai1ec_end-date-input">
							<?php _e( 'End date / time', AI1EC_PLUGIN_NAME ); ?>:
						</label>
					</td>
					<td>
						<input type="text" class="ai1ec-date-input ai1ec-form-control"
							id="ai1ec_end-date-input">
						<input type="text" class="ai1ec-time-input ai1ec-form-control"
							id="ai1ec_end-time-input">
						<input type="hidden"
							name="ai1ec_end_time"
							id="ai1ec_end-time"
							value="<?php echo $end->format_to_javascript(); ?>">
					</td>
				</tr>
				<?php
				$recurrence_attr = '';
				if ( $parent_event_id || $instance_id ) :
					$recurrence_attr = ' class="ai1ec-hide"';
				endif;
				?>
				<tr<?php echo $recurrence_attr; ?>>
					<td>
						<input type="checkbox" name="ai1ec_repeat" id="ai1ec_repeat"
								value="1"
								<?php echo $repeating_event ? 'checked' : ''; ?>>
						<input type="hidden" name="ai1ec_rrule" id="ai1ec_rrule"
								value="<?php echo $rrule; ?>">
						<label for="ai1ec_repeat" id="ai1ec_repeat_label">
							<?php _e( 'Repeat', AI1EC_PLUGIN_NAME );
								echo $repeating_event ? ':' : '...'; ?>
						</label>
					</td>
					<td>
						<div id="ai1ec_repeat_text" class="ai1ec_rule_text">
							<a href="#ai1ec_repeat_box"><?php echo $rrule_text; ?></a>
						</div>
					</td>
				</tr>
				<tr<?php echo $recurrence_attr; ?>>
					<td>
						<input type="checkbox" name="ai1ec_exclude" id="ai1ec_exclude"
								value="1"
								<?php echo $exclude_event ? 'checked' : ''; ?>
								<?php echo $repeating_event ? '' : 'disabled'; ?>>
						<input type="hidden" name="ai1ec_exrule" id="ai1ec_exrule"
								value="<?php echo $exrule; ?>">
						<label for="ai1ec_exclude" id="ai1ec_exclude_label">
							<?php _e( 'Exclude', AI1EC_PLUGIN_NAME );
								echo $exclude_event ? ':' : '...'; ?>
						</label>
					</td>
					<td>
						<div id="ai1ec_exclude_text" class="ai1ec_rule_text">
							<a href="#ai1ec_exclude_box"><?php echo $exrule_text; ?></a>
						</div>
						<span class="ai1ec-info-text">
							(<?php _e( 'Choose a rule for exclusion', AI1EC_PLUGIN_NAME ); ?>)
						</span>
					</td>
				</tr>
				<tr<?php echo $recurrence_attr; ?>>
					<td>
						<label for="ai1ec_exdate_calendar_icon" id="ai1ec_exclude_date_label">
							<?php _e( 'Exclude dates', AI1EC_PLUGIN_NAME ); ?>:
						</label>
					</td>
					<td>
						<div id="datepicker-widget">
							<div id="widgetField">
								<span></span>
								<a href="#"><?php _e( 'Select date range', AI1EC_PLUGIN_NAME ); ?></a>
							</div>
							<div id="widgetCalendar"></div>
						</div>
						<input type="hidden" name="ai1ec_exdate" id="ai1ec_exdate"
							value="<?php echo $exdate; ?>">
						<span class="ai1ec-info-text">
							(<?php _e( 'Choose specific dates to exclude', AI1EC_PLUGIN_NAME ); ?>)
						</span>
					</td>
				</tr>

				<?php // Recurrence modal skeleton ?>
				<div id="ai1ec_repeat_box" class="ai1ec-modal ai1ec-fade">
					<div class="ai1ec-modal-dialog">
						<div class="ai1ec-loading ai1ec-modal-content">
							<div class="ai1ec-modal-body ai1ec-text-center">
								<i class="ai1ec-fa ai1ec-fa-spinner ai1ec-fa-spin ai1ec-fa-3x"></i>
							</div>
						</div>
					</div>
				</div>

			</tbody>
		</table>
	</div>
</div>
