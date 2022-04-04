# yesfile 구매 서비스

yesfile 컨텐츠 구매, 구매리스트 정보 조회를 위한 서비스 API입니다.


## 요구사항

* docker 최신버전
* docker-compose
* git

## 개발환경 구성


###  소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-purchases.git
```


### 개발용 도커 환경 준비

1. 도커 네트워크 설정
```bash
$ docker network create yesfile
```

2. secrets 환경설정
> 파일의 위치는 프로젝트의 상위 디렉토리에 secrets 입니다.

| FILE | DESCRIPTION |
| ---- | ----------- |
| JWT_SECRET | JWT 토큰의 비밀키 |
| DB_ENV_MDB_PASSWORD | MDB 패스워드 |
| DB_ENV_SDB_PASSWORD | SDB 패스워드 |
| DB_ENV_COREDB_PASSWORD | COREDB 패스워드 |

3. 이미지 빌드
``` bash
$ docker build -t backend-api-purchases .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```


## API 기능 설명

| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/purchases | | 구매컨텐츠 정보 조회 | | |
| /v1/purchases/{id} | | 컨텐츠 재다운로드 | |
| /v1/purchases | 컨텐츠 구매 | | | |
| /v1/purchases/{id} | | | | 구매컨텐츠 삭제 |


## APi 상세 설명

### 구매리스트 정보 조회

  * URI
    * GET /v1/purchases
  * params
    * bbs_idx
    * category
    * limit
    * offset
  * return

```json
  {
    "count": 1,
    "purchases": [
        {
            "bbs_idx": "6073436",
            "idx": "2",
            "credit_check": "0",
            "target": "2",
            "billtype": "B",
            "point": "140",
            "packet": "0",
            "state": "0",
            "title": "[불리] 너무야한 충격적실화.무삭제판 bully ",
            "code": "BD_MV_06",
            "size": "1453509222",
            "grade": "2",
            "userid": "jmonaco",
            "usernick": "뱅크미디어",
            "userid_seller": "dmsalqkqh72",
            "seller_nick": "두통엔개고기",
            "expiredate": "1535171836",
            "regdate": "1534999016",
            "link_mobile": "1",
            "downlod_count": "0",
            "downlod_count_m": "2",
            "stream_count": "0",
            "copyid": "",
            "ip": "172.24.0.1",
            "expire_date": "46시간",
            "content": {
                "bbs_idx": "6073436",
                "title": "[불리] 너무야한 충격적실화.무삭제판 bully ",
                "userid": "dmsalqkqh72",
                "name": "두통엔개고기",
                "size": "1453509222",
                "chkcopy": "N",
                "code": "BD_MV_06",
                "code_cate2": "BD_MV",
                "point": 140,
                "contents": "<img ... >",
                "uploader_grade": "2",
                "chkiphone": "1",
                "cate1_kr": "영화",
                "cate2_kr": "공포/스릴러",
                "m_size": "548084245",
                "file_cnt": 2,
                "comment_score": 5.87,
                "comment_count": "182",
                "title_img": "",
                "nickname": "두통엔개고기",
                "category": "영화 > 공포/스릴러",
                "total_size": "499M"
            }
        },
        ...
    ]
}
```

### 컨텐츠 재다운로드

  * URI
    * GET /v1/purchases
  * params
    * id
  * return
  
```json
  {
    "contentFiles": [
      {
          "file_idx": "24580629",
          "realname": "Bully1.mp4",
          "size": "248493369",
          "title_img": "",
          "category": "BD_MV",
          "play_time_int": "3073",
          "mmsv_size": "733161472"
      },
      {
          "file_idx": "24580631",
          "realname": "Bully2.mp4",
          "size": "299590876",
          "title_img": "",
          "category": "BD_MV",
          "play_time_int": "3073",
          "mmsv_size": "720236544"
      }
    ],
    "count": 2
  }
```


### 컨텐츠 구매

  * URI
    * POST /v1/purchases
  * params
    * bbs_idx
    
### 구매컨텐츠 삭제

  * URI
    * DELETE /v1/purchases/{id}
  * params
    * id

## 에러코드
| 에러메세지 | 에러코드 | 설명 |
| :- | - | :- |
| ERROR_UNAUTHORIZED | 201 | 권한 없음 |
| ERROR_MEMBER_NOT_FOUND | 203 | 회원 정보 없음 |
| ERROR_SET_POINT | 204 | 포인트 수정 실패 |
| ERROR_NO_CONTENTS | 301 | 컨텐츠 정보 없음 |
| ERROR_NO_BOOKMARKS | 401 | 북마크 정보 없음 |
| ERROR_NO_PURCHASES | 501 | 구매목록 정보 없음 |
| ERROR_NO_POINT_J | 502 | 제휴컨텐츠 포인트 부족 |
| ERROR_NO_POINT_C | 503 | 일반컨텐츠 포인트 부족 |
| ERORR_IS_PURCHASE | 504 | 구매목록에 존재 |
| ERROR_NO_ACCOUNT | 505 | 충전내역이 없음 |
| ERROR_NO_DELETE_PURCHASES | 506 | 구매목록 삭제 실패 |
| ERROR_DOWN_EXCEEDED | 507 | 다운로드횟수 초과 |
| ERROR_ONLY_KOREA_AVAILABLE | 602 | 해외차단 |
| ERROR_PREVENT_MOBILE_DOWNLOAD | 603 | 모바일다운로드 금지 |
| ERROR_BLACKLIST_USER | 604 | 블랙리스트 |
| ERROR_FILTERING_FILE_CUT | 605 | 필터링요청 차단컨텐츠 |
| ERROR_INVALID_PARAM | 901 | 파라미터 유효성 검증 실패 |
| ERROR_SERVER_UNKNOWN | 999 | 알 수 없는 오류 |