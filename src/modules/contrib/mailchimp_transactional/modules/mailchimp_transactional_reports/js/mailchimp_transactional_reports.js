(function ($) {

Drupal.behaviors.mailchimp_transactional_reports = {
  attach: function (context, settings) {
    google.load("visualization", "1", {packages:["corechart"], "callback":drawCharts});

    function drawCharts() {
      var dataTableVol = new google.visualization.DataTable();
      dataTableVol.addColumn('datetime', Drupal.t('Date'));
      dataTableVol.addColumn('number', Drupal.t('Delivered'));
      dataTableVol.addColumn('number', Drupal.t('Bounced'));
      dataTableVol.addColumn('number', Drupal.t('Rejected'));

      for (var key in drupalSettings.mailchimp_transactional_reports.volume) {
        dataTableVol.addRow([
          new Date(drupalSettings.mailchimp_transactional_reports.volume[key]['date']),
          drupalSettings.mailchimp_transactional_reports.volume[key]['sent'],
          drupalSettings.mailchimp_transactional_reports.volume[key]['bounced'],
          drupalSettings.mailchimp_transactional_reports.volume[key]['rejected']
        ]);
      }

      var options = {
        pointSize: 5,
        hAxis: {format: 'MM/dd/y hh:mm aaa'}
      };

      var chart = new google.visualization.LineChart(document.getElementById('mailchimp_transactional-volume-chart'));
      chart.draw(dataTableVol, options);

      var dataTableEng = new google.visualization.DataTable();
      dataTableEng.addColumn('datetime', Drupal.t('Date'));
      dataTableEng.addColumn('number', Drupal.t('Open rate'));
      dataTableEng.addColumn('number', Drupal.t('Click rate'));

      for (var key in drupalSettings.mailchimp_transactional_reports.engagement) {
        dataTableEng.addRow([
          new Date(drupalSettings.mailchimp_transactional_reports.engagement[key]['date']),
          drupalSettings.mailchimp_transactional_reports.engagement[key]['open_rate'],
          drupalSettings.mailchimp_transactional_reports.engagement[key]['click_rate']
        ]);
      }

      var chart = new google.visualization.LineChart(document.getElementById('mailchimp_transactional-engage-chart'));
      chart.draw(dataTableEng, options);
    }
  }
}

})(jQuery);
