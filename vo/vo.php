<?php
    #region ParkCar(In/Out Count in Data) & Gate
    //History, Now 입/출차 정보
    class WB_PARKCAR_VO
    {
        public $idx;
        public $GateDate;
        public $GateSerial;
        public $CarNum;
    }

    //입/출 이미지 정보
    class WB_PARKCARIMG_VO
    {
        public $idx;
        public $CarNum_Img;
        public $CarNum_Imgname;
    }

    //차단기 제어 send 및 내역
    class WB_GATECONTROL_VO
    {
        public $GCtrCode;
        public $CD_DIST_OBSV;
        public $RegDate;
        public $Gate;
        public $GStatus;
    }

    //차단기 현재 상태 정보
    class WB_GATESTATUS_VO
    {
        public $CD_DIST_OBSV;
        public $RegDate;
        public $Gate;
    }

    //차단기(LPR) 그룹 정보 -> 그룹 지정이 되어있어야 현재 주차중인 차량 계산이 가능
    class WB_PARKGATEGROUP_VO
    {
        public $ParkGroupCode;
        public $ParkGroupName;
        public $ParkGroupAddr;
        public $ParkJoinGate;
        public $RegDate;
        public $GCode;
        public $GateArr;

        function Gate_array()
        {
            $this->GateArr = explode(",", $this->ParkJoinGate);
            return $this->GateArr;
        }
    }

    //주차중인 차량에게 메세지 발송위해 필요
    class WB_PARKSMSLIST_VO
    {
        public $idx;
        public $CarNum;
        public $CarPhone;
        public $SMSContent;
        public $RegDate;
        public $EndDate;
        public $SendStatus;
        public $SendType;
    }

    class WB_PARKCARMENT_VO
    {
        public $SMSMentCode;
        public $Title;
        public $Content;
    }

    class WB_PARKCARCNT_VO
	{
        public $ParkGroupCode;
		public $RegDate;
		public $MR0;
		public $MR1;
		public $MR2;
		public $MR3;
		public $MR4;
		public $MR5;
		public $MR6;
		public $MR7;
		public $MR8;
		public $MR9;
		public $MR10;
		public $MR11;
		public $MR12;
		public $MR13;
		public $MR14;
		public $MR15;
		public $MR16;
		public $MR17;
		public $MR18;
		public $MR19;
		public $MR20;
		public $MR21;
		public $MR22;
		public $MR23;
		public $MR24;
		public $DaySum;
    }
    #endregion
    #region Data
    class WB_DATA_VO
    {
        public string $type;
        public string $cdDistObsv;
        public int $subObsv = 0;
        public int $rainBit = 1;
        public DateTime $regDate;
        
        public array $data;
        public array $minData;
        public float $max;
        public float $min;
        public float $sum;
    }
    #endregion
?>