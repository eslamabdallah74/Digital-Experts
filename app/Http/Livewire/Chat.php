<?php

namespace App\Http\Livewire;

use App\Models\Message;
use App\Models\User;
use Livewire\Component;

class Chat extends Component
{
    public $users;
    public $allMessages = [];
    public $activeUserChat;

    public $search = '';

    public $message;



    public function mount()
    {

        if ($this->activeUserChat)
        {
            $this->allMessages  = Message::with('user')
            ->where(function($query){
                $query->where('from_user_id',auth()->user()->id)
                ->where('to_user_id',$this->activeUserChat->id);

            })->orWhere(function($query){
                $query->where('to_user_id',auth()->user()->id)
                ->where('from_user_id',$this->activeUserChat->id);
            })->get();
        }

    }

    public function getMessage($id)
    {
        $this->activeUserChat = User::where('id', $id)->first();
        $this->mount();
    }

    protected $rules = [
        'message' => 'required|min:1',
    ];

    // Decided to send the message even if it's bad but make it as stars(***)

    // protected $messages = [
    //     'message.profanity' => 'This is a bad word, Please be decent'
    // ];

    public function sendMessage()
    {
        $validatedData = $this->validate();

        $filterdMessage = app('profanityFilter')->filter($this->message);

        $sendMessage   = Message::create([
            'from_user_id'  => auth()->user()->id,
            'to_user_id'    => $this->activeUserChat->id,
            'message'       => $filterdMessage,
        ]);
        $this->reset('message');
        $this->mount();

    }

    public function render()
    {
        return view('livewire.chat', [
            $this->users        = User::where('id', '!=', auth()->user()->id)
            ->where('name','like','%'.$this->search.'%')->orderBy('id','desc')->get()
        ]);
    }
}
