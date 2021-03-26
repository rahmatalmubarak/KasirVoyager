<?php

namespace App\Http\Controllers\Voyager;

use App\Models\Detail;
use App\Models\Product;
use App\Models\Transaction;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataDeleted;
use TCG\Voyager\Events\BreadDataRestored;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Events\BreadImagesDeleted;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;

class TransactionController extends VoyagerBaseController
{
    use BreadRelationshipParser;



    //***************************************
    //                _____
    //               |  __ \
    //               | |__) |
    //               |  _  /
    //               | | \ \
    //               |_|  \_\
    //
    //  Read an item of our Data Type B(R)EAD
    //
    //****************************************

  

    //***************************************
    //                ______
    //               |  ____|
    //               | |__
    //               |  __|
    //               | |____
    //               |______|
    //
    //  Edit an item of our Data Type BR(E)AD
    //
    //****************************************

 

    //***************************************
    //
    //                   /\
    //                  /  \
    //                 / /\ \
    //                / ____ \
    //               /_/    \_\
    //
    //
    // Add a new item of our Data Type BRE(A)D
    //
    //****************************************

    public function create(Request $request)
    {
        $transaksi = Transaction::count();
        $id=0;
        
            if ($transaksi != 0) {
                $id = Transaction::latest('id')->first();
                $ids = $id->id;
         }else{
             $ids=0;
            }
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;

        foreach ($dataType->addRows as $key => $row) {
            $dataType->addRows[$key]['col_width'] = $row->details->width ?? 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'add', $isModelTranslatable);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }
        $product = Product::all();

        return Voyager::view($view, compact('product', 'ids', 'isModelTranslatable'));
    }

    /**
     * POST BRE(A)D - Store data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
       
        // dd($request->all());          
        // $request->validate([
        //     'idAkun' => 'required',
        //     'produk' => 'required',
        //     'jumlah' => 'required'
        //     ]);
            try{
            DB::transaction(function () use($request) {
                $hasil = 0;
                $tambah = 0;
                $tambah2 = 0;
                $stok = 0;
                for ($i=0; $i < count($request->jumlah); $i++) { 
                        $sell = DB::table('products')->where('id',$request->produk[$i])->value('sell');
                        $capital = DB::table('products')->where('id',$request->produk[$i])->value('capital');
                        $stok = DB::table('products')->where('id',$request->produk[$i])->value('stock');
                        $tambah = ($request->jumlah[$i] * $sell);
                        $tambah2 = $request->jumlah[$i] * $capital;
                        $hasil = $tambah - $tambah2;
                        $stok2 = $request->jumlah[$i];
                        product::where('id', $request->produk[$i])->update([
                           'stock' => $stok - $stok2
                           ]);
                             }
                            // dd($s);
                            $transaksi = new Transaction();
                            $transaksi->id = $request->id;  
                            $transaksi->users_id = $request->idAkun;
                            $transaksi->date = now();
                            $transaksi->profit = $hasil;
                            $transaksi->save();

                            $jm=0;
                            for ($i=0; $i < count($request->jumlah); $i++) { 
                                $product = product::where('id', $request->produk[$i])->first();
                                
                                if ($product->stock >= $request->jumlah[$i]) {
                                    $jm +=1;
                                }
                            }
                            
                                  for ($i=0; $i < count($request->jumlah); $i++) { 
                                      $sell = DB::table('products')->where('id',$request->produk[$i])->value('sell');
                                             $data = new Detail();
                                             $data->product_id = $request->produk[$i];
                                             $data->transaction_id = $request->id;
                                             $data->total = $request->jumlah[$i];
                                             $data->total_price = $request->jumlah[$i] * $sell;
                                             $data->save(); 
                                            }
                                            DB::commit();
                              if($jm == count($request->jumlah)) {
                                        DB::commit(); 
                                    }else{
                                        // dd($request->all());
                                        DB::rollBack();
                                        return redirect()->back()->with('error', 'Transaksi Gagal!!');
                                    }
                                });
                                return redirect('admin/transactions')->with('status', 'Transaksi Berhasil');
                                            
                                            
                                        } catch (\Throwable $th) {
                                            DB::rollBack();
                                            throw $th;
                                            return redirect()->back()->with('error', 'Transaksi Gagal!!');
                                            
                                        }
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item BREA(D)
    //
    //****************************************

    public function tampil(Request $request) 
    {
        $id = $request->get('id');
        if($request->ajax()) {
        $data = '';
        $user = DB::select("SELECT * FROM users where id='$id'");;
    
        // dd($user);
        foreach($user as $usr)
        {
            $data = array(
                'nama' => $usr->name
            );
        }
        echo json_encode($data);
    }
    }

    public function cetak($id) 
    {
        // dd($id);
        $cetak = Detail::join('products', 'products.id', '=', 'details.product_id')
        ->where('transaction_id', $id)
        ->get();
        $pdf = \PDF::loadView('cetak', ['modal' => $cetak]);
        return $pdf->download();
        
    }
}

