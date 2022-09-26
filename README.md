# Nettigo Air Monitor 2 JSON consumer
pho page that consumes the data provided by a [Nettigo Air Monitor 2](https://nettigo.eu/products/nettigo-air-monitor-kit-0-3-2-build-your-own-smog-sensor)
python module to interact and read data from C.M.I. logger from [Technische Alternative](https://www.ta.co.at)

## local testing
```sh
# create database and user
$> sudo --user=postgres createuser --no-createdb --no-createrole --no-superuser --pwprompt <USER>
$> sudo --user=postgres createdb --encoding=UTF-8 --owner=<USER> <DATABASE>

# create schema for database
$> cat schema.sql | psql --host=<HOST> --dbname=<DATABASE> <USER>

# configure database
$> cat > database.ini <<EOF
[postgresql]
host=<HOST>
port=5432
dbname=<DATABASE>
user=<USER>
password=<PASSWORD>
sslmode=require
EOF

# start local testing server
$> php --docroot="${PWD}/src" --server="127.0.0.1:8080"

# POST json
$> curl --data "@${PWD}/example.json" --header "Content-Type: application/json" --request POST --verbose http://127.0.0.1:8080/index.php
```

