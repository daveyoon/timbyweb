<h2>TIMBY API Activity</h2>

<div class="api_activity">
  <form method="post" class="frm_date_filter">
    <h4>Filter by date</h4>
    <label for="">
      From: 
      <input type="text" name="start_date" class="datepicker" autocomplete="off" value="<?php echo date('j F, Y', strtotime($range['start'])) ?>">
    </label>
    <label for="">
      To: 
      <input type="text" name="end_date" class="datepicker" autocomplete="off" value="<?php echo date('j F, Y', strtotime($range['end'])) ?>">
    </label>
    <?php wp_nonce_field('do_filter_date','filter_date')  ?>
    <input type="submit" class="button" value="Filter">
  </form>

  <table class="wp-list-table fixed widefat">
    <thead>
      <tr>
        <th>Date</th>
        <th>Request URL</th>
        <th>Request Parameters</th>
        <th>Response</th>
        <th>Client IP</th>
      </tr>
    </thead>
    <tbody>
      <?php if( count($logs) > 0 ) { ?>
        <p><strong>Total API Calls: <?php echo count($logs) ?></strong></p>
        <?php foreach ($logs as $log) { $data = json_decode($log->log) ?>
          <tr>
            <td><?php echo date('j F, Y', strtotime($log->created_at)) ?></td>
            <td><?php echo $data->request->url ?></td>
            <td>
              <?php echo json_encode($data->request->parameters) ?>
            </td>
            <td>
              <?php echo $data->response->body ?>
            </td>
            <td><?php echo $data->request->ip ?></td>            
          </tr>
        <?php } ?>
      <?php } ?>
      <tfoot>
        <tr>
          <th>Date</th>
          <th>Request URL</th>
          <th>Request Parameters</th>
          <th>Response</th>
          <th>Client IP</th>
        </tr>
      </tfoot>
    </tbody>
  </table>  
</div>
