# yesfile 컨텐츠 서비스

yesfile 컨텐츠 정보 조회를 위한 서비스 API입니다.

## 요구사항

* docker 최신버전
* docker-compose
* git

## 개발환경 구성

### 소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-contents.git
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
$ docker build -t backend-api-contents .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

## API 기능 설명
| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/contents | | 컨텐츠 정보 조회 | | |
| /v1/contents/{id}/files | | 컨텐츠의 파일들 정보 조회 | | |
| /v1/contents/downurl | | 다운로드 URL 요청 | | |

## APi 상세 설명

### 컨텐츠 정보 조회

  * URI
    * GET /v1/contents
  * params
    * id
    * category
    * limit
    * offset
  * response

```json
{
    "count": 1,
    "contents": [
        {
            "bbs_idx": "8689500",
            "title": "재밋는태국영화[ATM 에러] 사내연애가 금지되어있는은행",
            "userid": "dosting2",
            "name": "dosting2",
            "size": "1565475562",
            "chkcopy": "Y",
            "code": "BD_MV_01",
            "code_cate2": "BD_MV",
            "point": 1000,
            "contents": "<image ...>",
            "uploader_grade": "3",
            "chkiphone": "1",
            "cate1_kr": "영화",
            "cate2_kr": "최신/미개봉",
            "m_size": "455904599",
            "file_cnt": 1,
            "comment_score": 0,
            "comment_count": "0",
            "title_img": "http://image.yesfile.com/data/2013/03/17/img_3489668_1.jpg",
            "nickname": "dosting2",
            "category": "영화 > 최신/미개봉",
            "total_size": "537M",
            "jehyu_contents": [
                {
                    "idx": "17791150",
                    "copyid": "finemovie1",
                    "set_point": "1000",
                    "bbs_idx": "8689500",
                    "file_idx": "32023934",
                    "use_chk": "Y",
                    "down_chk": "0",
                    "copyno": "521923",
                    "contents_id": "SG0000041290",
                    "summary": "ATM",
                    "inning": ""
                }
            ]
        },
        ...
    ]
}
```

    
### 컨텐츠의 파일들 정보 조회

  * URI
    * GET /v1/contents/{id}/files
  * params
    * userid
  * response

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


### 다운로드 URL 요청

  * URI
    * GET /v1/contents/downurl
  * params
    * id
    * file_idx
  * response
  
```json
{
    "down_url": "..."
}
```


## 에러 코드
| 에러메세지 | 에러코드 | 설명 |
| :- | - | :- |
| ERROR_NO_CONTENTS | 301 | 컨텐츠 정보 없음 | 
| ERROR_NO_MOBILE | 302 | 모바일 컨텐츠 아님 | 
| ERROR_DOWNLOAD_POLICY | 303 | url policy 실패 | 
| ERROR_NO_PURCHASES | 501 | 구매목록 정보 없음 | 
| ERROR_INVALID_PARAM | 901 | 파라미터 유효성 검증 실패| 
| ERROR_SERVER_UNKNOWN | 999 | 알 수 없는 오류 | 