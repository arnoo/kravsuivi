ln "$REMOTE_PATH/delivered/current/data/"*.sqlite3 "$DELIVERY_PATH/data/"
chown -R www-data "$DELIVERY_PATH/data"
