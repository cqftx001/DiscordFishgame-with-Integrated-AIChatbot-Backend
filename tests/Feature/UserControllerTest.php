<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\TestCase;
use App\Http\Controllers\UserController;
use Mockery;
use App\Models\Users;

class UserControllerTest extends TestCase
{
    use WithFaker;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_exists()
    {
        // 模拟请求对象
        $request = Request::create('/user/is-exist', 'GET', ['user_id' => 1]);

        // 模拟查询构建器
        $queryBuilderMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $queryBuilderMock->shouldReceive('where')
            ->with('user_id', 1)
            ->andReturnSelf();
        $queryBuilderMock->shouldReceive('first')
            ->andReturn((object) ['user_id' => 1]);

        // 使用 Mockery 模拟 Users 模型
        $userMock = Mockery::mock('overload:' . Users::class);
        $userMock->shouldReceive('where')
            ->with('user_id', 1)
            ->andReturn($queryBuilderMock);

        // 创建控制器实例
        $controller = new UserController();

        // 调用控制器方法
        $response = $controller->existOrNot($request);

        // 验证响应
        $responseData = json_decode($response->getContent(), true);
        // 验证响应
        $this->assertEquals(200, $responseData['code']);
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'code' => 200,
                'msg' => 'User exists',
                'data' => ['exist' => 1]
            ]),
            $response->getContent()
        );
    }

    public function test_user_not_exists()
    {
        // 模拟请求对象
        $request = Request::create('/user/is-exist', 'GET', ['user_id' => 999]);

        // 模拟查询构建器
        $queryBuilderMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $queryBuilderMock->shouldReceive('where')
            ->with('user_id', 999)
            ->andReturnSelf();
        $queryBuilderMock->shouldReceive('first')
            ->andReturn(null);

        // 使用 Mockery 模拟 Users 模型
        $userMock = Mockery::mock('overload:' . Users::class);
        $userMock->shouldReceive('where')
            ->with('user_id', 999)
            ->andReturn($queryBuilderMock);

        // 创建控制器实例
        $controller = new UserController();

        // 调用控制器方法
        $response = $controller->existOrNot($request);
        $responseData = json_decode($response->getContent(), true);
        // 验证响应
        $this->assertEquals(404, $responseData['code']);
    }
}
