@extends('layouts.app')
@section('title', '首页')

@section('content')
  欢迎用户{{ $user_id }}!
  当前商品：{{ $product->name }}
  价格：{{ $product->price }}

  <div>
    <!-- 输出后端报错开始 -->
    @if (count($errors) > 0)
      <div class="alert alert-danger">
        <h4>有错误发生：</h4>
        <ul>
          @foreach ($errors->all() as $error)
            <li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <addresses inline-template>
      <form class="form-horizontal" role="form" id="order-form">
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">收货人姓名：</label>
          <div class="col-sm-9 col-md-7">
            <input type="text" name="contact_name" class="form-control" placeholder="填写收货人姓名">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">收货人手机：</label>
          <div class="col-sm-9 col-md-7">
            <input type="text" name="contact_mobile" class="form-control" placeholder="填写收货人手机号码">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">省市区：</label>
          <!-- inline-template 代表通过内联方式引入组件 -->
          <select-district @change="onDistrictChanged" inline-template>
            <div class="col-sm-9 col-md-7 row">
              <div class="col-sm-3">
                <select class="form-control" v-model="provinceId">
                  <option value="">选择省</option>
                  <option v-for="(name, id) in provinces" :value="id">@{{ name }}</option>
                </select>
              </div>
              <div class="col-sm-3">
                <select class="form-control" v-model="cityId">
                  <option value="">选择市</option>
                  <option v-for="(name, id) in cities" :value="id">@{{ name }}</option>
                </select>
              </div>
              <div class="col-sm-3">
                <select class="form-control" v-model="districtId">
                  <option value="">选择区</option>
                  <option v-for="(name, id) in districts" :value="id">@{{ name }}</option>
                </select>
              </div>
            </div>
          </select-district>
          <input type="hidden" name="province" v-model="province">
          <input type="hidden" name="city" v-model="city">
          <input type="hidden" name="district" v-model="district">
        </div>

        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">详细地址：</label>
          <div class="col-sm-9 col-md-7">
            <input type="text" name="address" class="form-control" placeholder="填写详细收货地址, 如：丽都商务中心D座519">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">备注：</label>
          <div class="col-sm-9 col-md-7">
            <textarea name="remark" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="offset-sm-3 col-sm-3">
            <button type="button" class="btn btn-primary btn-create-order">提交订单</button>
          </div>
        </div>
      </form>
    </addresses>
  </div>
@stop

@section('scriptsAfter')
  <script type="text/javascript">
    $(document).ready(function () {
      $('.btn-create-order').click(function () {

        var req = {
          product_id: {{ $product->id }},
          seller_id: {{ $seller_id }},
          contact_name: $("#order-form").find('input[name=contact_name]').val(),
          contact_mobile: $("#order-form").find('input[name=contact_mobile]').val(),
          province: $("#order-form").find('input[name=province]').val(),
          city: $("#order-form").find('input[name=city]').val(),
          district: $("#order-form").find('input[name=district]').val(),
          address: $("#order-form").find('input[name=address]').val(),
        };

        var remark = $("#order-form").find('textarea[name=remark]').val();
        if (remark != '') req['remark'] = remark;
        console.log(req)

        axios.post('{{ route('orders.store') }}', req)
          .then(function (response) {
            if (response.data.code) {
              swal(response.data.msg, '', 'error');
            } else {
              swal('订单提交成功', '', 'success');
            }
          }, function (error) {
            if (error.response.status === 422) {
              var html = '<div>';
              _.each(error.response.data.errors, function (errors) {
                _.each(errors, function (error) {
                  html += error + '<br>';
                })
              });
              html += '</div>';
              swal({content: $(html)[0], icon: 'error'})
            } else {
              swal('系统错误', '', 'error');
            }
          })
      });
    });
  </script>
@endsection


