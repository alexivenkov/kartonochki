<?php

include_once __DIR__ . '/../config.inc.php';
include_once __DIR__ . '/../modules/M_DB.inc.php';

class CsvDumper
{
    /** @var MSQL $db */
    protected $db;

    /** @var  string $path */
    protected $path;

    public function __construct($path)
    {
        $this->db = MSQL::Instance();
        $this->path = $path;
    }

    public function dump()
    {
        $csv = array_map('str_getcsv', file($this->path));

        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        array_shift($csv);

        foreach ($csv as $item) {
            $data = array_combine([
                'id','full_name', 'city_name', 'region', 'center', 'nal_sum_limit', 'eng_name', 'post_code_list'
            ],array_values($item));

            if(count(explode(',', $data['full_name'])) < 2) {
                $data['full_name'] = trim($data['full_name'], ',');
            }

            $data['center'] = $data['center'] !== '' ? $data['center'] : null;
            $data['nal_sum_limit'] = $data['nal_sum_limit'] === 'no limit' ? null : $data['nal_sum_limit'];

            $this->db->Insert('geo_data', $data);
        }
    }
}

$db = M_DB::Instance();
$path = $argv[1];

$dumper = new CsvDumper($path);
$dumper->dump();