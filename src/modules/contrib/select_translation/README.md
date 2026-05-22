# Select translation
*Select translation implements a Views filter to select which translation of a node should be displayed in a list of nodes.*

Several modes are available to select a translation:
- Use the current interface language; if not available use the original node language.
- Use the current interface language; if not available use the default site language; if not available use the original node language.
- Specify a list of languages, and use the most appropriate language.

Some special values are recognized in the languages list:
- "current" will be replaced with the current interface language;
- "default" will be replaced with the default site language;
- "original" will be replaced with the original node language.

Select translation also provides an API following the same selection algorithm as the Views filter.

## Credits:

Based on the drupal 7 code by Alexandru Croitor (Placinta) which includes a much
better Views filter handler that uses left joins instead of correlated
(dependent) sub-queries, which should prove to work much faster when there are
a lot of nodes in the database.
https://www.drupal.org/user/176134

Big thanks to Alice Heaton for the original D6 module.
https://drupal.org/user/60899

citronica and citronica's husband for providing the initial port to D7.
https://drupal.org/user/354488

zuuperman for the option_definition fix.
https://drupal.org/user/361625

The helpful people at stack overflow that helped optimize the query.
http://stackoverflow.com/questions/21985917/optimize-mysql-query-with-dependent-sub-query/21986190?noredirect=1#comment33324875_21986190
