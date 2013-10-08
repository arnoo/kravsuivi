ln "$REMOTE_PATH/delivered/current/*.sqlite3" "$DELIVERY_PATH/data"
chown -R www-data "$DELIVERY_PATH/data"
