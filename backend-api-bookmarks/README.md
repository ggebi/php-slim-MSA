# yesfile 컨텐츠 서비스

yesfile 북마크(찜목록) 정보 조회를 위한 서비스 API입니다.

## 요구사항

* docker 최신버전
* docker-compose
* git

## 개발환경 구성

### 소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-bookmarks.git
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
$ docker build -t backend-api-bookmarks .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

## API 기능 설명
| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/bookmarks | - | 북마크 정보 조회 | - | - |
| /v1/bookmarks/{id} | - | - | - | 북마크 삭제 |
| /v1/bookmarks | 북마크 추가 | - | - | - |

## APi 상세 설명

### 북마크(찜목록) 정보 조회

  * URI
    * GET /v1/bookmarks
  * params
    * id
    * category
    * limit
    * offset
  * response

```json
{
  "count": 8,
  "bookmarks": [
    {
      "bbs_idx": "9975655",
      "idx": "8",
      "userid": "jmonaco",
      "title": "진짜사나이.E28.131020_iMrel_720P",
      "size": "1052568257",
      "code": "BD_UC_01",
      "uploader": "mbc8",
      "content": {
        "bbs_idx": "9975655",
        "title": "진짜사나이.E28.131020_iMrel_720P",
        "userid": "mbc8",
        "name": "mbc8",
        "size": "1601076856",
        "chkcopy": "Y",
        "code": "BD_UC_01",
        "code_cate2": "BD_UC",
        "point": 300,
        "contents": "\r\n<DIV><IMG onerror=imgErr(this) src=\"http://image.yesfile.com/data/2013/10/25/1382675119_SUOD.jpg\" width=700 height=2538></DIV>\r\n<DIV>&nbsp;</DIV><BR>",
        "uploader_grade": "5",
        "chkiphone": "1",
        "cate1_kr": "동영상",
        "cate2_kr": "오락",
        "m_size": "1052568257",
        "file_cnt": 1,
        "comment_score": 10,
        "comment_count": "1",
        "title_img": "http://image.yesfile.com/data/2013/10/25/img_4160632_1.jpg",
        "nickname": "mbc8",
        "category": "동영상 > 오락",
        "total_size": "550M",
        "jehyu_contents": [
          {
              "idx": "16763761",
              "copyid": "imbc",
              "set_point": "300",
              "bbs_idx": "9975655",
              "file_idx": "35284561",
              "use_chk": "Y",
              "down_chk": "0",
              "copyno": "810112",
              "contents_id": "iMBC_00108160028",
              "summary": "일밤 2부 진짜 사나이",
              "inning": "28"
          },
          ...
        ],
      }
    },
    ...
  ]
}
```
    
### 북마크 삭제

  * URI
    * DELETE /v1/bookmarks/{id}
  * params
    * id

### 북마크 추가

  * URI
    * POST /v1/bookmarks
  * params
    * bbs_idx


## 에러 코드
| 에러메세지 | 에러코드 | 설명 |
| :- | - | :- |
| ERROR_NO_CONTENTS | 301 | 컨텐츠 정보 없음 |
| ERROR_NO_BOOKMARKS | 401 | 북마크 정보 없음 |
| ERROR_NO_DELETE_BOOKMARKS | 402 | 북마크 삭제 실패 |
| ERROR_DUPLICATED | 403 | 북마크 중복 |
| ERROR_INVALID_PARAM | 901 | 파라미터 유효성 검증 실패 |
| ERROR_SERVER_UNKNOWN | 999 | 알 수 없는 오류 |