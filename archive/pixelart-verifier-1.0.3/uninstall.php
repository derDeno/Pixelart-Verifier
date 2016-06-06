<?php

delete_option('px_verifiy_settings');

delete_metadata('user', 0,'px_envato_username', '', true);
delete_metadata('user', 0,'px_envato_purchase_date', '', true);
delete_metadata('user', 0,'px_envato_purchase_code', '', true);
delete_metadata('user', 0,'px_envato_license', '', true);
delete_metadata('user', 0,'px_envato_item', '', true);
delete_metadata('user', 0,'px_envato_support_amount', '', true);
delete_metadata('user', 0,'px_envato_support_until', '', true);


?>