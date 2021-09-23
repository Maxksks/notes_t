<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use DataTables;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax())
        {
            return DataTables::class::make(Note::latest()->get())
                ->addColumn('action', 'notes.action')
                ->rawColumns(['action'])
                ->editColumn('created_at', function($row){
                    return e( $row->created_at->format('H:i d/m/Y'));
                })
                ->make(true);
        }

        return view('notes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:30',
            'description' => 'required|max:100'
        ]);

        Note::updateOrCreate(['id' => $request->id],
                            $request->only(['title', 'description']));

        return response()->json(['success' => 'Заметка добавлена!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return response()->json(Note::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Note::find($id)->delete();
        return response()->json(['success' => 'Заметка удалена!']);
    }
}
