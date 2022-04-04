# yesfile 회원 정보 서비스

mobile service의 사용자 정보 조회를 위한 서비스 API입니다.

## 요구사항

* docker 최신버전
* docker-composer
* git

## 개발환경 구성

###소스코드 다운로드
``` bash
$ git clone ssh://git@git.bankmedia.co.kr:10022/yesfile/backend-api-members.git
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
$ docker build -t backend-api-members .
```

4. 로컬 테스트용 개발환경 컨테이너 실행
``` bash
$ docker-compose up
```

## API 기능 설명
| HTTP METHOD | POST | GET | PUT | DELETE |
| ----------- | ---- | --- | --- | ------ |
| /v1/members | - | 여러명의 유저정보 조회 | - | - |
| /v1/members/{userid} | - | 한명의 유저정보 조회 | - | - |
| /v1/members/{userid}/verify | 아이디 패스워드 검증 | - | - | - |
| /v1/members/{userid}/point | - | 포인트 조회 | - | - |
| /v1/members/{userid}/point | 포인트 수정 | - | - | - |
| /v1/members/{userid}/configs | - | 알림 설정 조회 | - | - |
| /v1/members/{userid}/configs | 알림 설정 수정 | - | - | - |
| /v1/members/{userName}/blacklist | - | 블랙리스트 조회 | - | - |

## APi 상세 설명

### 여러명의 유저 정보 조회

  * URI
    * POST /v1/members
  * responseCode & Data
    * 200 - OK array{ 회원정보 }
    * 400 - Bad Request 
    * 401 - Unauthorized
    
### 한명의 회원 정보 조회

  * URI
    * GET /v1/members/{userid}
  * params
    * userid
  * response

```json
{
    "idx": "27",
    "userid": "jmonaco",
    "userpw": "...",
    "name": "",
    "level": "9",
    "nickname": "뱅크미디어",
    "jumin": "",
    "email": "",
    "tel": "",
    "hpcorp": "",
    "hp": "",
    "zipcode": "",
    "address": "",
    "recomid": null,
    "mailing": "1",
    "auth": "1",
    "adult": "1",
    "ip": "",
    "ip_last": null,
    "connect": "0",
    "last_connect": "0",
    "note": "",
    "cp": "0",
    "bandate": "0",
    "control": "0",
    "pg_id": null,
    "flag_admin": "0",
    "regdate": "1304352767",
    "join_site": "0",
    "chk_iphone": "0",
    "member_key": null,
    "notice_date": "0"
}
```

### 패스워드 검증

  * URI
    * GET /v1/members/{userid}/verify/{userpw}
  * params
    * userid
    * userpw
  * response

```json
{
    "level": "9"
}
```


### 포인트 조회

  * URI
    * GET /v1/members/{userid}/point
  * response

```json
{
    "userid": "jmonaco",
    "packet": "99000.00",
    "reward": "0.00",
    "item": "0.00",
    "point": "99520.00",
    "keeping": "0.00",
    "recom": "0.00",
    "mileage": "1000",
    "coupon": "0",
    "thiat": "0",
    "reward_bonus": "25",
    "use_halfmonth": "0",
    "pre_halfmonth": "0",
    "disk_space": "0",
    "fix_start": "0",
    "fix_end": "0",
    "fix_sub_end": "0",
    "fix_mode": "F",
    "fix_time": "S",
    "time_start": "24",
    "time_end": "24",
    "auto": "N",
    "auto_cancel": "N"
}
```


### 포인트 수정

  * URI
    * POST /v1/members/{usrid}/point
  * params
    * point_name
    * point


### 알림 설정 조회

  * URI
    * GET /v1/members/{userid}/configs
  * response

```json
{
    "push_on": "1"
}
```


### 알림 설정 수정

  * URI
    * POST /v1/members/{userid}/configs
  * params
    * push_on
    * agree_private


### 블랙리스트 조회

  * URI
    * GET /v1/members/{userid}/blacklist
  * response

```json
{
    "blacklist": {
        "idx": "1",
        "name": "나라호고다",
        "target_name": "뱅크미디어",
        "memo": "그냥테스트",
        "state": "Y",
        "regdate": "1534812272"
    }
}
```


## 에러 코드
| 에러메세지 | 에러코드 | 설명 |
| :- | - | :- |
| ERROR_UNAUTHORIZED | 201 | 권한 없음 |
| ERROR_VERIFY_PASSWORD | 202 | 회원 검증 실패 |
| ERROR_MEMBER_NOT_FOUND | 203 | 회원 정보 없음 |
| ERROR_SET_POINT | 204 | 포인트 수정 실패 |
| ERROR_SET_CONFIG | 205 | 설정 변경 실패 |
| ERROR_INVALID_PARAM | 901 | 파라미터 유효성 검증 실패 |
| ERROR_SERVER_UNKNOWN | 999 | 알 수 없는 오류 |