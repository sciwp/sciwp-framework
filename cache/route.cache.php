<?php return array (
  'cars\\/(?P<model>[a-z]{4})\\/(?P<make>[A-Za-z0-9\\-\\_]+)\\/?$' => 1,
  'news\\/(?P<permalink>[A-Za-z0-9\\-\\_]+)\\/?$' => 'news',
  'blog2\\/(?P<zipcode>[a-z]{2})\\/?(?P<zipcode2>[A-Za-z0-9\\-\\_]+)?\\/?$' => 2,
  'phones\\/(?P<brand>[a-z]{1,6})\\/(?P<model>[A-Za-z0-9\\-\\_]+)\\/?$' => 3,
);