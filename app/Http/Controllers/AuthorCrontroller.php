<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use PharIo\Manifest\Author;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class AuthorCrontroller extends Controller
{
    public function index()
    {
        // $response = $this->getResponseSuccess();
        // //$book = Book::all();
        // $book = Book::with('category','editorial')->get();
        // $response['data']  = $book;
        // return $response;
        $authors = Author::with('books')->orderBy('name', 'asc')->get();
        return $this->getResponse200($authors);
    }

    public function store(Request $request)
    {
        try {
                $author = new Author();
               // $autor->isbn = $isbn;
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                $author->second_surname = $request->second_surname;
                //$autor->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                $author->save();
                return $this->getResponse201('author', 'created', $author);

        } catch (Exception $e) {
            return $this->getResponse500([]);
        }
    }
    public function update(Request $request,$id)
    {
        try {
                $author = Author::find($id);
               // $autor->isbn = $isbn;
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                $author->second_surname = $request->second_surname;
                //$autor->published_date = date('y-m-d h:i:s'); //Temporarily assign the current date
                $author->update();
                return $this->getResponse201('author', 'updated', $author);

        } catch (Exception $e) {
            return $this->getResponse500([]);
        }
    }
    public function show(Request $request, $id){
        $author = Author::find($id);
        try {
            if ($author) {
                $author = Author::with('books')->orderBy('name', 'asc')->get();
                return $this->getResponse200($author);
            }else {
                return $this->getResponse500(['Not found ']);
            }

        } catch (Exception $e) {
            return $this->getResponse500(['Not found']);
        }
        //$response=$this->getResponse();


    }
    public function destroy(Request $request, $id){
        $author = Author::find($id);
        try {
            if ($author) {
                 foreach ($author->books as $item) { //Associate authors to book (N:M relationship)
                    $author->books()->detach($item->id);
                 }
                //$author->books->delete();
                $author->delete();
                return $this->getResponseDelete200($author);
            }else {
                return $this->getResponse404(['Not found 1']);
            }

        } catch (Exception $e) {
            return $this->getResponse500([$e->getMessage()]);
        }
        //$response=$this->getResponse();


    }

}
