/* global google */
(function () {
  Drupal.behaviors.mailchimp_transactional_reports = {
    attach() {
      function drawCharts() {
        const dataTableVol = new google.visualization.DataTable();
        dataTableVol.addColumn('datetime', Drupal.t('Date'));
        dataTableVol.addColumn('number', Drupal.t('Delivered'));
        dataTableVol.addColumn('number', Drupal.t('Bounced'));
        dataTableVol.addColumn('number', Drupal.t('Rejected'));

        Object.keys(
          drupalSettings.mailchimp_transactional_reports.volume,
        ).forEach((key1) => {
          dataTableVol.addRow([
            new Date(
              drupalSettings.mailchimp_transactional_reports.volume[key1].date,
            ),
            drupalSettings.mailchimp_transactional_reports.volume[key1].sent,
            drupalSettings.mailchimp_transactional_reports.volume[key1].bounced,
            drupalSettings.mailchimp_transactional_reports.volume[key1]
              .rejected,
          ]);
        });

        const options = {
          pointSize: 5,
          hAxis: { format: 'MM/dd/y hh:mm aaa' },
        };

        const chart1 = new google.visualization.LineChart(
          document.getElementById('mailchimp_transactional-volume-chart'),
        );
        chart1.draw(dataTableVol, options);

        const dataTableEng = new google.visualization.DataTable();
        dataTableEng.addColumn('datetime', Drupal.t('Date'));
        dataTableEng.addColumn('number', Drupal.t('Open rate'));
        dataTableEng.addColumn('number', Drupal.t('Click rate'));

        Object.keys(
          drupalSettings.mailchimp_transactional_reports.engagement,
        ).forEach((key2) => {
          dataTableEng.addRow([
            new Date(
              drupalSettings.mailchimp_transactional_reports.engagement[
                key2
              ].date,
            ),
            drupalSettings.mailchimp_transactional_reports.engagement[key2]
              .open_rate,
            drupalSettings.mailchimp_transactional_reports.engagement[key2]
              .click_rate,
          ]);
        });

        const chart2 = new google.visualization.LineChart(
          document.getElementById('mailchimp_transactional-engage-chart'),
        );
        chart2.draw(dataTableEng, options);
      }

      google.load('visualization', '1', {
        packages: ['corechart'],
        callback: drawCharts,
      });
    },
  };
})(jQuery);
