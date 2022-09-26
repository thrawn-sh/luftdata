CREATE TABLE IF NOT EXISTS measurement (
    time             TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
    sensor_id        INTEGER                  NOT NULL                             , -- esp8266id
    firmware         VARCHAR                  NOT NULL                             , -- software_version
    bmp_pressure     REAL                     NOT NULL                             , -- sensordatavalues.BMP_pressure
    bmp_temperature  REAL                     NOT NULL                             , -- sensordatavalues.BMP_temperature
    heca_humidity    REAL                     NOT NULL                             , -- sensordatavalues.HECA_humidity
    heca_temperature REAL                     NOT NULL                             , -- sensordatavalues.HECA_temperature
    pm_10            REAL                     NOT NULL                             , -- sensordatavalues.SDS_P1
    pm_25            REAL                     NOT NULL                             , -- sensordatavalues.SDS_P2
    sht_humidity     REAL                     NOT NULL                             , -- sensordatavalues.SHT3X_humidity
    sht_temperature  REAL                     NOT NULL                             , -- sensordatavalues.SHT3X_temperature
    wifi_signal      SMALLINT                 NOT NULL                             , -- sensordatavalues.signal
    UNIQUE           (time, sensor_id)
);
