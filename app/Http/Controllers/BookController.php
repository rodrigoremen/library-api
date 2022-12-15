<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookDownloads;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        // $response = $this->getResponseSuccess();
        // //$book = Book::all();
        // $book = Book::with('category','editorial')->get();
        // $response['data']  = $book;
        // return $response;
        $books = Book::orderBy('title', 'asc')->get();
        return $this->getResponse200($books);
    }

    public function store(Request $request)
    {
        try {
            $isbn = preg_replace('/\s+/', '', $request->isbn); //Remove blank spaces from ISBN
            $existIsbn = Book::where("isbn", $isbn)->exists(); //Check if a registered book exists (duplicate ISBN)
            if (!$existIsbn) { //ISBN not registered
                $book = new Book();
                $book->isbn = $isbn;
                $book->title = $request->title;
                $book->description = $request->description;
                $book->published_date = Carbon::now();
                //$book->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                $book->category_id = $request->category["id"];
                $book->editorial_id = $request->editorial["id"];
                $book->save();
                $bookDownload= new BookDownloads();
                $bookDownload->book_id=$book->id;
                $bookDownload->save();
                foreach ($request->authors as $item) { //Associate authors to book (N:M relationship)
                    $book->authors()->attach($item);
                }
                $book = Book::with('bookDownload','category', 'editorial', 'authors')->where("id", $book->id)->get();
                 //$book = Book::with('category', 'editorial', 'authors')->where("id", $id)->get();
                return $this->getResponse201('book', 'created', $book);
            } else {
                return $this->getResponse500(['The isbn field must be unique']);
            }
        } catch (Exception $e) {
            return $this->getResponse500([$e]);
        }
    }

    public function update(Request $request, $id)
    {
        //$response = $this->response();
        $book = Book::find($id);
        DB::beginTransaction();
        try {
            if ($book) {
                $isbn = trim($request->isbn);
                $Myisbn = Book::where("isbn", $isbn)->first();
                //return "lll";
                if (!$Myisbn || $Myisbn->id == $book->id) {
                    //return "lll";
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = Carbon::now();
                    //$book->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                    $book->category_id = $request->category["id"];
                    $book->editorial_id = $request->editorial["id"];
                    $book->update();
                    //elimina
                    foreach ($book->authors as $item) { //Associate authors to book (N:M relationship)
                        $book->authors()->detach($item->id);
                    }
                    foreach ($request->authors as $item) { //Associate authors to book (N:M relationship)
                        $book->authors()->attach($item);
                    }
                    //return "";
                    $book = Book::with('bookDownload','category', 'editorial', 'authors')->where("id", $id)->get();
                    DB::commit();
                    return $this->getResponse201('book', 'updated', $book);

                } else {
                    // return "!df";
                     return $this->getResponse500(['ISBN duplicated!']);
                    //$response["message"] = "ISBN duplicated!";
                }
            } else {
                //$response["message"] = "ISBN duplicated!";

                 return $this->getResponse500(['Not found']);
            }
            // return "llls";
             //DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
           //return $this->getResponse500([]);
            // $response["message"] = "Rollback transaction";
            return $this->getResponse500(['Rollback transaction']);

        }
    }
    // public function update2(Request $request, $id){
    //     DB::beginTransaction;
    //     try {

    //         DB::commit();
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //     }
    // }
    public function show(Request $request, $id){
        $book = Book::find($id);
        try {
            if ($book) {
                $book = Book::with('category', 'editorial', 'authors')->where("id", $id)->get();
                return $this->getResponse200($book);
            }else {
                return $this->getResponse404();
            }

        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
        //$response=$this->getResponse();


    }
    public function destroy(Request $request, $id){
        $book = Book::find($id);
        try {
            if ($book) {
                foreach ($book->authors as $item) { //Associate authors to book (N:M relationship)
                    $book->authors()->detach($item->id);
                }
                //$bookD = BookDownloads::find($book->id);
                $book->bookDownload()->delete();
                $book->delete();
                return $this->getResponseDelete200("book");
            }else {
                return $this->getResponse404();
            }

        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
        //$response=$this->getResponse();


    }

}
