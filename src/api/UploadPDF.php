<?php
function savePDFByFile($filePDF){
    if(isset($filePDF)){
        $charactersWithoutAccents = array('Š'=>'S', 'š'=>'s', 'Ð'=>'Dj',''=>'Z', ''=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A','Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I','Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U','Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a','å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i','ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u','ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f','ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T', '´'=> '', '`' => ''
        );
        $nameFile = str_replace(" ","_",$filePDF['name']);
        $nameFile = strtr($nameFile, $charactersWithoutAccents);
        $nameFile = strtr($nameFile, [".pdf" => '']);
        $nameFile = $nameFile.time().'.pdf';

        if($filePDF['type'] != "application/pdf"){
            throw new AppException('Envie um PDF!');
        }

        $folderUpload = realpath(dirname(__FILE__).'/../../data/pdf').'/';
        $file = $folderUpload . $nameFile;
        $tmp = $filePDF['tmp_name'];

        if(move_uploaded_file($tmp, $file)){
            unset($filePDF);
            return $nameFile;
        } else {
            throw new AppException('Falha ao salvar o PDF!');
        }
    }
}