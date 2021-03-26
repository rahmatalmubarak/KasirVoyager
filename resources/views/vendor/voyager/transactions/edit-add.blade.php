@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title', 'Add Transactions')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-basket"></i>
        {{'Add Transaction'}}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-bordered">
                    <!-- form start -->
                    <form role="form"
                            class="form-edit-add"
                            action="{{ route('store') }}"
                            method="POST" enctype="multipart/form-data">
                        <!-- PUT Method if we are editing -->
                        {{-- @if($edit)
                            {{ method_field("PUT") }}
                        @endif --}}

                        <!-- CSRF TOKEN -->
                        {{ csrf_field() }}

                        <div class="panel-body">

                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif


                           
                                <!-- GET THE DISPLAY OPTIONS -->
                            
                                <div class="form-group">
                                    <div class="hide">
                                        <input type="text" class="form-control" name="id" id="id" value="{{$ids+1}}" hidden> 
                                    </div>
                                    <label class="control-label" for="name">ID Customer</label>
                                    <input type="text" class="form-control" onkeyup="otomatis()" name="idAkun" id="idAkun" placeholder="input ID Akun" value="{{old('idAkun')}}">
                                    <div class="col-md">
                                        <div class="form-floating">
                                          <label for="floatingInputGrid">Nama Customer</label>
                                            <input type="text" class="form-control" name="nama" id="nama"  placeholder="" readonly>
                                        </div>
                                      </div>
                                      <div class="copy">
                                          <div class="control-group">
                                            <div class="card-body">
                                              <div class="col-md">
                                                <div class="form-floating">
                                                  <label for="floatingInputGrid">Produk</label>
                                                  <select name="produk[]" id="produk" class="form-control">
                                                    @foreach ($product as $produk)<option value="{{$produk->id}}">{{$produk->name}}</option>@endforeach
                                                  </select>                
                                                </div>
                                              </div>
                                              <div class="col-md">
                                                <div class="form-floating">
                                                  <label for="floatingInputGrid">Jumlah</label>
                                                    <input type="text" class="form-control" name="jumlah[]" id="jumlah" placeholder="input Jumlah">
                                                  </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </div> 
                                      <div class="card-body">
                                        <button class="btn btn-success add-more" type="button">
                                          <i class="glyphicon glyphicon-plus"></i> Add
                                        </button>
                                      </div>
                                    <div class="control-group after-add-more"> 
                                    </div>
                                    <!-- /.card-body -->
                                    
                                    <div class="card-footer">
                                      <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                    </div>
                                  </div>
                                  
                                </div>
                        </div><!-- panel-body -->

                        
                    </form>

                    <iframe id="form_target" name="form_target" style="display:none"></iframe>
                    

                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script>
    $(document).ready(function() {
      $(".add-more").click(function(){ 
          var html = $(".copy").html();
          $(".after-add-more").after(html);
      });
      // saat tombol remove dklik control group akan dihapus 
      $("body").on("click",".remove",function(){ 
          $(this).parents(".control-group").remove();
      });
    });
    // 
    function otomatis() {
        var id = $("#idAkun").val();
        $.ajax({
            url: '{{route('otomatis')}}',
            method : 'GET',
            data: "id=" + id,
            success: function (data) {
                var json = data,
                    obj = JSON.parse(json);
                $('#nama').val(obj.nama);
            }
        });
    };

    </script>
@stop
