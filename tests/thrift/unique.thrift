namespace php app.processor

struct TestRequest {
    1: i32 id,
    2: string name,
    3: list<i64> lists,
}

struct TestResponse {
    1: i32 status
}

service ThriftService {
    TestResponse test1(1: TestRequest request),
    i32 test2(1: string name, 2: i32 id),
    i32 test3(1: TestRequest request, 2: i32 id),
}

service HproseService {
    TestResponse test1(1: TestRequest request),
    i32 test2(1: string name, 2: i32 id),
    i32 test3(1: TestRequest request, 2: i32 id),
}

service SwooleService {
    TestResponse test1(1: TestRequest request),
    i32 test2(1: string name, 2: i32 id),
    i32 test3(1: TestRequest request, 2: i32 id),
}