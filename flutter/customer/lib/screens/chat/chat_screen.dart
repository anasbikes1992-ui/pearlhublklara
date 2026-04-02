import 'package:flutter/material.dart';

class ChatScreen extends StatefulWidget {
  final String channel;
  final String listingId;
  final String receiverId;
  final String receiverName;

  const ChatScreen({
    super.key,
    required this.channel,
    required this.listingId,
    required this.receiverId,
    this.receiverName = 'Provider',
  });

  @override
  State<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends State<ChatScreen> {
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();
  final List<Map<String, dynamic>> _messages = [];
  bool _loading = true;
  String _selectedLang = 'en';

  static const Map<String, String> _languages = {
    'en': 'English',
    'si': 'Sinhala',
    'ta': 'Tamil',
    'hi': 'Hindi',
    'ar': 'Arabic',
    'zh': 'Chinese',
    'fr': 'French',
    'de': 'German',
    'es': 'Spanish',
    'ja': 'Japanese',
  };

  @override
  void initState() {
    super.initState();
    _loadMessages();
  }

  Future<void> _loadMessages() async {
    setState(() => _loading = false);
    // Messages would be loaded via ChatService
  }

  void _sendMessage() {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    setState(() {
      _messages.add({
        'id': DateTime.now().millisecondsSinceEpoch.toString(),
        'message': text,
        'sender_id': 'current_user',
        'is_voice': false,
        'created_at': DateTime.now().toIso8601String(),
      });
    });
    _messageController.clear();
    _scrollToBottom();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.receiverName, style: const TextStyle(fontSize: 16)),
            Text('Pre-booking chat', style: TextStyle(fontSize: 12, color: Colors.grey[400])),
          ],
        ),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.language),
            onSelected: (lang) => setState(() => _selectedLang = lang),
            itemBuilder: (context) => _languages.entries
                .map((e) => PopupMenuItem(value: e.key, child: Text(e.value)))
                .toList(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Language indicator
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
            color: Colors.blue.shade50,
            child: Row(
              children: [
                const Icon(Icons.translate, size: 14, color: Colors.blue),
                const SizedBox(width: 4),
                Text(
                  'Auto-translating to ${_languages[_selectedLang]}',
                  style: TextStyle(fontSize: 12, color: Colors.blue[700]),
                ),
              ],
            ),
          ),

          // Messages
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : _messages.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.chat_bubble_outline, size: 64, color: Colors.grey[300]),
                            const SizedBox(height: 16),
                            Text('Start a conversation', style: TextStyle(color: Colors.grey[500])),
                          ],
                        ),
                      )
                    : ListView.builder(
                        controller: _scrollController,
                        padding: const EdgeInsets.all(16),
                        itemCount: _messages.length,
                        itemBuilder: (context, index) {
                          final msg = _messages[index];
                          final isMine = msg['sender_id'] == 'current_user';

                          return Align(
                            alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              constraints: BoxConstraints(
                                maxWidth: MediaQuery.of(context).size.width * 0.75,
                              ),
                              decoration: BoxDecoration(
                                color: isMine ? Theme.of(context).primaryColor : Colors.grey[200],
                                borderRadius: BorderRadius.only(
                                  topLeft: const Radius.circular(16),
                                  topRight: const Radius.circular(16),
                                  bottomLeft: Radius.circular(isMine ? 16 : 4),
                                  bottomRight: Radius.circular(isMine ? 4 : 16),
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (msg['is_voice'] == true)
                                    Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(Icons.mic, size: 16,
                                            color: isMine ? Colors.white70 : Colors.grey[600]),
                                        const SizedBox(width: 4),
                                        Text('Voice message',
                                            style: TextStyle(
                                                fontSize: 11,
                                                color: isMine ? Colors.white70 : Colors.grey[600])),
                                      ],
                                    ),
                                  Text(
                                    msg['message'] ?? '',
                                    style: TextStyle(
                                      color: isMine ? Colors.white : Colors.black87,
                                      fontSize: 14,
                                    ),
                                  ),
                                  if (msg['translated_text'] != null)
                                    Padding(
                                      padding: const EdgeInsets.only(top: 4),
                                      child: Text(
                                        msg['translated_text'],
                                        style: TextStyle(
                                          fontSize: 12,
                                          fontStyle: FontStyle.italic,
                                          color: isMine ? Colors.white60 : Colors.grey[500],
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
          ),

          // Input bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 4, offset: const Offset(0, -2))],
            ),
            child: SafeArea(
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.mic, color: Colors.grey),
                    onPressed: () {
                      // Voice recording would be implemented here
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Voice recording - hold to record')),
                      );
                    },
                  ),
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Type a message...',
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide(color: Colors.grey[300]!),
                        ),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        isDense: true,
                      ),
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _sendMessage(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  CircleAvatar(
                    backgroundColor: Theme.of(context).primaryColor,
                    child: IconButton(
                      icon: const Icon(Icons.send, color: Colors.white, size: 20),
                      onPressed: _sendMessage,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }
}
