<?php
class  hb_security_code_generator_security_export_csv_class{
    private $csv_array = [],$code_data,$hb_name;

    public  function export_csv_init($code_data,$hb_name)
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');
        $this->code_data = $code_data;
        $this->hb_name = $hb_name;


        $count = count($this->code_data);
        $this->csv_array[] = array($count);
        $this->csv_array[] = array('Product', 'Code');
        $filename = "contest-data-export-" . date("Y-m-d-H-i-s") . ".csv";
        header( 'Content-Description: File Transfer' );
        header( "Content-Disposition: attachment; filename={$filename}" );
        header( 'Expires: 0' );
        header('Content-Type: application/csv; charset=UTF-8');
        ob_clean();
        $this->export_csv();

    }

    public function generator_code($len_of_gen_str,$hb_no,$hb_rule){
        if($hb_rule=='1'){
            $chars = "0123456789";
        }elseif($hb_rule=='2'){
            $chars = "OABCDEFGHIJKLMNPQRSTUVWXYZ0123456789";
            if($hb_no=='true'){
                $chars = substr($chars,1);
            }
        }else{
            $chars = "oOABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz0123456789";
            if($hb_no=='true'){
                $chars = substr($chars,2);
            }
        }
        $chars = str_shuffle($chars);
        $var_size = strlen($chars);
        $str = '';
        for( $x = 0; $x < $len_of_gen_str; $x++ ) {
            $random_str= $chars[ rand( 0, $var_size - 1 ) ];
            $str .= $random_str;
        }
        return $str;

    }
    private function export_csv(){
        $count = count($this->code_data);

        for ($i = 0; $i < $count; $i++) {
            $p = $this->code_data[$i];
            $this->csv_array[] = array(
                $this->hb_name,$p[1]
            );
        }
        $fh = @fopen( 'php://output', 'w' );
        foreach ( $this->csv_array as $data_row ) {
            //UTF8
            fprintf($fh, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv( $fh, $data_row );

        }
//        fseek($fh, 0);
//        fpassthru($fh);
        fclose( $fh );





    }
}