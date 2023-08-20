<?php
    require_once("dao_t.php");

    #region GATE DAO
    class WB_GATECONTROL_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_gatecontrol", "GCtrCode DESC");
        } 

        public function FailQuery($idx)
        {
            $this->sql = "UPDATE {$this->table} SET GStatus = 'fail' WHERE GCtrCode = {$idx}";
            return $this->Execute($this->sql);
        }
    }

    class WB_GATESTATUS_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_gatestatus", "CD_DIST_OBSV");
        } 
    }

    class WB_PARKCARHIST_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkcarhist", "idx DESC", "WB_PARKCAR_VO");
        }

        function insertCarInfo(WB_PARKCAR_VO $vo)
        {
            $obj = (object) array(
                "GateDate" => $vo->GateDate,
                "GateSerial" => $vo->GateSerial,
                "CarNum" => $vo->CarNum,
                
                "uGateDate" => $vo->GateDate,
                "uGateSerial" => $vo->GateSerial,
                "uCarNum" => $vo->CarNum
            );

            try
            {
                $this->sql = "INSERT INTO {$this->table} (GateDate, GateSerial, CarNum) VALUES (:GateDate, :GateSerial, :CarNum) ";
                $this->sql .= "ON DUPLICATE KEY UPDATE ";
                $this->sql .= "GateDate = :uGateDate, ";
                $this->sql .= "GateSerial = :uGateSerial, ";
                $this->sql .= "CarNum = :uCarNum";
    
                $this->Prepare($obj);
            }
            catch (Exception $e) { }
        }
    }

    class WB_PARKCARNOW_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkcarnow", "idx DESC", "WB_PARKCAR_VO");
        } 

        function insertCarInfo(WB_PARKCAR_VO $vo)
        {
            $obj = (object) array(
                "idx" => $vo->idx,
                "GateDate" => $vo->GateDate,
                "GateSerial" => $vo->GateSerial,
                "CarNum" => $vo->CarNum,

                "uidx" => $vo->idx,
                "uGateDate" => $vo->GateDate,
                "uGateSerial" => $vo->GateSerial
            );

            try
            {
                $this->sql = "INSERT INTO {$this->table} (idx, GateDate, GateSerial, CarNum) ";
                $this->sql .= "VALUES (:idx, :GateDate, :GateSerial, :CarNum) ";
                $this->sql .= "ON DUPLICATE KEY UPDATE ";
                $this->sql .= "idx = :uidx, ";
                $this->sql .= "GateDate = :uGateDate, ";
                $this->sql .= "GateSerial = :uGateSerial";

                $this->Prepare($obj);
            }
            catch (Exception $e) { }
        }

        public function delete3DaysInfo()
        {
            $date = date("Y-m-d", strtotime("-3 day"));
            return $this->Delete("GateDate <= '{$date}'");
        }
        
        public function outCarDelete($CarNum)
        {
            $obj = (object) array("CarNum" => $CarNum);

            try
            {
                $this->sql = "DELETE FROM {$this->table} WHERE CarNum = :CarNum";
                
                $this->Prepare($obj);
            }
            catch (Exception $e) { }
        }
    }

    class WB_PARKCARIMG_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkcarimg", "idx DESC");
        }

        function insertCarImg(WB_PARKCARIMG_VO $vo)
        {
            try
            {
                $this->sql = "INSERT INTO {$this->table} (idx, CarNum_Img, CarNum_Imgname) ";
                $this->sql .= "VALUES (:idx, :CarNum_Img, :CarNum_Imgname) ";
                $this->sql .= "ON DUPLICATE KEY UPDATE ";
                $this->sql .= "idx = :idx, ";
                $this->sql .= "CarNum_Img = :CarNum_Img, ";
                $this->sql .= "CarNum_Imgname = :CarNum_Imgname";
    
                $this->Prepare($vo);
            }
            catch (Exception $e) { }
        }
    }

    class WB_PARKGATEGROUP_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkgategroup", "ParkGroupCode");
        }     
    }

    class WB_PARKSMSLIST_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parksmslist", "idx DESC");
        }    
    }

    class WB_PARKSMSMENT_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parksmsment", "idx DESC");
            $this->addInsert();
        }    

        function addInsert()
        {
            $this->sql = "SELECT * FROM {$this->table}";
            $cnt = $this->Query();

            if (count($cnt) < 1)
            {
                $ment = "[재난안전문자] 둔치주차장 침수위험! 신속 이동주차 요망! -재난안전대책본부-";
                $this->Execute("INSERT INTO {$this->table} (Title, Content) VALUES ('침수위험알림', '{$ment}')");
            }
        }  
    }

    class WB_PARKCARINCNT_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkcarincnt", "RegDate DESC", "WB_PARKCARCNT_VO");
        }

        function carCountUp(WB_PARKCARCNT_VO $vo, $MR)
        {
            try
            {
                $this->sql = "INSERT INTO {$this->table} (ParkGroupCode, RegDate, {$MR}, DaySum) ";
                $this->sql .= "VALUES ('{$vo->ParkGroupCode}', '{$vo->RegDate}', 1, 1) ";
                $this->sql .= "ON DUPLICATE KEY UPDATE ";
                $this->sql .= "{$MR} = {$MR} + 1, ";
                $this->sql .= "DaySum = DaySum + 1";
    
                $this->Execute();
            }
            catch (Exception $e) { }
        }

        function carCountUpColNull(WB_PARKCARCNT_VO $vo, $MR)
        {
            $this->sql = "UPDATE {$this->table} SET {$MR} = 1, DaySum = 1 WHERE ParkGroupCode = '{$vo->ParkGroupCode}' AND RegDate = '{$vo->RegDate}'";
            
            $this->Execute();
        }
    }

    class WB_PARKCAROUTCNT_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("wb_parkcaroutcnt", "RegDate DESC", "WB_PARKCARCNT_VO");
        }

        function carCountDown(WB_PARKCARCNT_VO $vo, $MR)
        {
            try
            {
                $this->sql = "INSERT INTO {$this->table} (ParkGroupCode, RegDate, {$MR}) ";
                $this->sql .= "VALUES ('{$vo->ParkGroupCode}', '{$vo->RegDate}', 1) ";
                $this->sql .= "ON DUPLICATE KEY UPDATE ";
                $this->sql .= "{$MR} = {$MR} + 1, ";
                $this->sql .= "DaySum = DaySum + 1";
    
                $this->Execute();
            }
            catch (Exception $e) { }
        }

        function carCountDownColNull(WB_PARKCARCNT_VO $vo, $MR)
        {
            $this->sql = "UPDATE {$this->table} SET {$MR} = 1, DaySum = 1 WHERE ParkGroupCode = '{$vo->ParkGroupCode}' AND RegDate = '{$vo->RegDate}'";
            
            $this->Execute();
        }
    }
    #endregion
    #region DATA
    class WB_DATA_DAO extends DAO_T
    {
        function __construct()
        {
            parent::__construct("data", "RegDate DESC", "WB_DATA_VO");
        }

        public function Select1Hour(WB_DATA_VO $vo)
        {
            $data = array_fill(0, 60, null);
            $y = $vo->regDate->format("Y");
            $table = "wb_{$vo->type}1min_{$y}";

            if ($vo->subObsv > 0) $dplaceWhere = " AND SUB_OBSV = '{$vo->subObsv}'";
            else $dplaceWhere = "";

            try
            {
                $sql = "SELECT * FROM {$table} WHERE CD_DIST_OBSV = '{$vo->cdDistObsv}' AND RegDate = '{$vo->regDate->format("YmdH")}' {$dplaceWhere}";
                $res = $this->ArrayToSingle($sql);
                if ($res !== false)
                {
                    $data = explode("/", $res["MRMin"]);

                    if (count($data) != 60)
                    {
                        $data = array_fill(0, 60, null);
                    }
                    else
                    {
                        for ($i = 0; $i < 60; $i++)
                        {
                            $data[$i] = floatval($data[$i]);
                        }
                    }

                    $vo->data = $data;
                    $vo->sum = array_sum($data);
                    $vo->max = max($data);
                    $vo->min = min($data);
                    return $vo;
                }
            }
            catch (Exception $ex)
            {
                return false;
            }

        }

        public function Select1Day(WB_DATA_VO $vo)
        {
            $data = array_fill(0, 24, null);
            $table = "wb_{$vo->type}1hour";

            if ($vo->subObsv > 0) $dplaceWhere = " AND SUB_OBSV = '{$vo->subObsv}'";
            else $dplaceWhere = "";

            try
            {
                $sql = "SELECT * FROM {$table} WHERE CD_DIST_OBSV = '{$vo->cdDistObsv}' AND RegDate = '{$vo->regDate->format("Ymd")}' {$dplaceWhere}";
                $res = $this->ArrayToSingle($sql);
                if ($res !== false)
                {
                    for ($i = 1; $i <= 24; $i++)
                    {
                        if ($res["MR{$i}"] !== "")
                        {
                            $floatVal = floatval($res["MR{$i}"]);
                            $data[$i - 1] = $floatVal;
                        }
                    }

                    $vo->data = $data;
                    $vo->sum = array_sum($data);
                    $vo->max = max($data);
                    $vo->min = min($data);

                    return $vo;
                }
            }
            catch (Exception $ex)
            {
                return false;
            }
        }

        public function Select1Month(WB_DATA_VO $vo)
        {
            $dplaceWhere = "";
            $column = null;
            $table = "wb_{$vo->type}1hour";

            $data = array_fill(0, intval($vo->regDate->format("t")), null);
            $minData = array_fill(0, intval($vo->regDate->format("t")), null);

            try
            {
                switch ($vo->type)
                {
                    case "rain":
                        $column = "DaySum";
                        break;

                    case "water":
                        $column = "DayMax, DayMin";
                        break;

                    case "dplace":
                        $dplaceWhere = " AND SUB_OBSV = '{$vo->subObsv}'";
                    case "soil":
                    case "snow":
                    case "tilt":
                        $column = "DayMax";
                        break;

                    default:
                    break;
                }

                $sql = "SELECT RegDate, {$column} FROM {$table} WHERE CD_DIST_OBSV = '{$vo->cdDistObsv}' AND MONTH(RegDate) = {$vo->regDate->format("m")} {$dplaceWhere}";
                $res = $this->SQLTOARRAY($sql);
                if ($res !== false)
                {
                    foreach ($res as $row)
                    {
                        $regDate = new DateTime($row["RegDate"]);
                        $idx = intval($regDate->format("d")) - 1;

                        switch ($vo->type)
                        {
                            case "rain":
                                $data[$idx] = floatval($row["DaySum"]);
                                break;
                                
                            case "water":
                                $data[$idx] = floatval($row["DayMax"]);
                                $minData[$idx] = floatval($row["DayMin"]);
                                break;

                            case "dplace":
                            case "soil":
                            case "snow":
                            case "tilt":
                                $data[$idx] = floatval($row["DayMax"]);
                                break;

                            default:
                                break;
                        }                        
                    }

                    $vo->data = $data;
                    $vo->minData = $vo->type == "water" ? $minData : array();
                    $vo->sum = array_sum($data);
                    $vo->max = max($data);
                    $vo->min = min($data);
                    $vo->min = $vo->type == "water" ? min($minData) : min($data);

                    return $vo;
                }
            }
            catch (Exception $ex)
            {
                return false;
            }
        }

        public function Select1Year(WB_DATA_VO $vo)
        {
            $dplaceWhere = "";
            $column = null;
            $table = "wb_{$vo->type}1hour";

            $data = array_fill(0, 12, null);
            $minData = array_fill(0, 12, null);

            try
            {
                switch ($vo->type)
                {
                    case "rain":
                        $column = "SUM(DaySum) as sum";
                        break;

                    case "water":
                        $column = "MAX(DayMax) as max, MIN(DayMin) as min";
                        break;

                    case "dplace":
                        $dplaceWhere = " AND SUB_OBSV = '{$vo->subObsv}'";
                    case "soil":
                    case "snow":
                    case "tilt":
                        $column = "MAX(DayMax) as max";
                        break;

                    default:
                    break;
                }

                $sql = "SELECT RegDate, {$column} FROM {$table} WHERE CD_DIST_OBSV = '{$vo->cdDistObsv}' {$dplaceWhere}";
                $sql .= "GROUP BY MONTH(RegDate) HAVING YEAR(RegDate) = {$vo->regDate->format("Y")}";
                $res = $this->SQLToArray($sql);
                if ($res !== false)
                {
                    foreach ($res as $row)
                    {
                        $regDate = new DateTime($row["RegDate"]);
                        $idx = intval($regDate->format("m")) - 1;

                        switch ($vo->type)
                        {
                            case "rain":
                                $data[$idx] = floatval($row["sum"]);
                                break;
                                
                            case "water":
                                $data[$idx] = floatval($row["max"]);
                                $minData[$idx] = floatval($row["min"]);
                                break;

                            case "dplace":
                            case "soil":
                            case "snow":
                            case "tilt":
                                $data[$idx] = floatval($row["max"]);
                                break;

                            default:
                                break;
                        }                        
                    }

                    $vo->data = $data;
                    $vo->minData = $vo->type == "water" ? $minData : array();
                    $vo->sum = array_sum($data);
                    $vo->max = max($data);
                    $vo->min = min($data);
                    $vo->min = $vo->type == "water" ? min($minData) : min($data);

                    return $vo;
                }
            }
            catch (Exception $ex)
            {
                return false;
            }
        }

        public function SelectDis(WB_DATA_VO $vo)
        {
            try
            {
                if ($this->existTable("wb_{$vo->type}dis"))
                {
                    $sql = "SELECT * FROM wb_{$vo->type}dis WHERE CD_DIST_OBSV = '{$vo->cdDistObsv}'".($vo->type == "dplace" ? " AND SUB_OBSV = '{$vo->subObsv}'" : "");
                    $res = $this->ArrayToSingle($sql);

                    if ($res !== false)
                    {
                        switch ($vo->type)
                        {
                            case "rain":
                                $vo->regDate = new DateTime($res["RegDate"]);
                                $vo->data["yester"] = $res["rain_yester"];
                                $vo->data["today"] = $res["rain_today"];
                                $vo->data["now"] = $res["rain_hour"];
    
                                $vo->data["mov_1h"] = $res["mov_1h"];
                                $vo->data["mov_2h"] = $res["mov_2h"];
                                $vo->data["mov_3h"] = $res["mov_3h"];
                                $vo->data["mov_6h"] = $res["mov_6h"];
                                $vo->data["mov_12h"] = $res["mov_12h"];
                                $vo->data["mov_24h"] = $res["mov_24h"];
                                break;
                            
                            case "water":
                                $vo->regDate = new DateTime($res["RegDate"]);
                                $vo->data["yester"] = $res["water_yester"];
                                $vo->data["today"] = $res["water_today"];
                                $vo->data["now"] = $res["water_now"];
                                break;

                            case "dplace":
                                $vo->regDate = new DateTime($res["RegDate"]);
                                $vo->data["yester"] = $res["dplace_yester"];
                                $vo->data["today"] = $res["dplace_today"];
                                $vo->data["now"] = $res["dplace_now"];

                                $vo->data["change"] = $res["dplace_change"];
                                $vo->data["speed"] = $res["dplace_speed"];
                                break;

                            case "snow":
                                $vo->regDate = new DateTime($res["RegDate"]);
                                $vo->data["yester"] = $res["snow_yester"];
                                $vo->data["today"] = $res["snow_today"];
                                $vo->data["now"] = $res["snow_hour"];
                                break;

                            case "soil":
                            case "tilt":
                            case "flood":
                                $vo->regDate = new DateTime($res["RegDate"]);
                                $vo->data["yester"] = $res["yester"];
                                $vo->data["today"] = $res["today"];
                                $vo->data["now"] = $res["now"];
                                break;

                            default:
                                break;
                        }
                    }
                    else
                    {
                        $currentTime = $vo->regDate;
                        $today = $this->Select1Day($vo);
                        $todayIdx = intval($currentTime->format("H"));

                        $vo->regDate = $currentTime->sub(new DateInterval("P1D"));
                        $yester = $this->Select1Day($vo);

                        $minIdx = intval($currentTime->format("m"));

                        switch ($vo->type)
                        {
                            case "rain":
                                $vo->data["yester"] = $yester->sum;
                                $vo->data["today"] = $today->sum;
                                $vo->data["now"] = $today->data[$todayIdx];

                                $movIndexArr = [1, 2, 3, 6, 12, 24];
                                $moveData = array();

                                for ($i = 24; $i > 1; $i--)
                                {
                                    $moveTime = $currentTime->sub(new DateInterval("P{$i}H"));
                                    $vo->regDate = $moveTime;

                                    $_data = $this->Select1Hour($vo);
                                    $moveData = array_merge($moveData, $_data->data);
                                }

                                foreach ($movIndexArr as $h)
                                {
                                    $range = (24 * 60) - ($h * 60) + $minidx;

                                    $_data = $moveData;
                                    $_data = range($range, $minIdx);

                                    $vo->data["mov_{$h}"] = sum($_data);
                                }
                                break;

                            case "dplace":
                                $this->table = "wb_dplace1hour";
                                $standVo = $this->SQLToArray("CD_DIST_OBSV = '{$vo->cdDistObsv}' AND SUB_OBSV = '{$vo->subObsv}'", "RegDate DESC");

                                $standVal = null;
                                foreach ($standVo as $svo)
                                {
                                    for ($i = 1; $i <= 24; $i++)
                                    {
                                        try
                                        {
                                            if (floatval($svo["MR{$i}"]) > 0)
                                            {
                                                $standVal = floatval($svo["MR{$i}"]);
                                                break;
                                            }
                                        }
                                        catch (Exception $ex)
                                        {
                                            continue;
                                        }
                                    }
                                    
                                    if ($standVal !== null) break;
                                }
                                $vo->data["change"] = abs($standVal - $today->data[$todayIdx]);

                                $h = 1;
                                $m = $minIdx;
                                $before1HourVo = new WB_DATA_VO();
                                while (true)
                                {
                                    try
                                    {
                                        if ($h == 1 || $m >= 60)
                                        {
                                            $vo->regDate = $currentTime->sub(new DateInterval("P{$h}H"));
                                            $before1HourVo = $this->Select1Hour($vo);
    
                                            $h++;
                                            $m = 0;
                                        }
                                        else if (floatval($before1HourVo->data[$i]) > 0)
                                        {
                                            $beforeVal = floatval($before1HourVo->data[$i]);
                                            break;
                                        }
                                        else if ($h > 5);
                                        {
                                            $beforeVal = $today->data[$todayIdx];
                                            break;
                                        }
                                    }
                                    catch (Exception $ex)
                                    {
                                        continue;
                                    }

                                    $m++;
                                }

                                $vo->data["speed"] = abs($beforeVal - $today->data[$todayIdx]);
                            case "water":
                            case "snow":
                            case "soil":
                            case "tilt":
                            case "flood":
                                $vo->data["yester"] = $yester->max;
                                $vo->data["today"] = $today->max;
                                $vo->data["now"] = $today->data[$todayIdx];
                                break;
                        }
                    }
                }

                return $vo;
            }
            catch (Exception $ex)
            {
                return false;
            }
        }
    }
    #endregion
