import 'dart:convert';
import 'package:http/http.dart' as http;

class ChatMessage {
  final String id;
  final String senderId;
  final String receiverId;
  final String message;
  final String? translatedText;
  final bool isVoice;
  final String? voiceUrl;
  final DateTime createdAt;

  ChatMessage({
    required this.id,
    required this.senderId,
    required this.receiverId,
    required this.message,
    this.translatedText,
    this.isVoice = false,
    this.voiceUrl,
    required this.createdAt,
  });

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      id: json['id'] ?? '',
      senderId: json['sender_id'] ?? '',
      receiverId: json['receiver_id'] ?? '',
      message: json['message'] ?? '',
      translatedText: json['translated_text'],
      isVoice: json['is_voice'] ?? false,
      voiceUrl: json['voice_url'],
      createdAt: DateTime.parse(json['created_at'] ?? DateTime.now().toIso8601String()),
    );
  }
}

class ChatService {
  final String baseUrl;
  final String token;

  ChatService({required this.baseUrl, required this.token});

  Map<String, String> get _headers => {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  Future<List<ChatMessage>> getConversation(String channel, {int limit = 50}) async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/chat/$channel?limit=$limit'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final list = data['data'] as List? ?? [];
      return list.map((m) => ChatMessage.fromJson(m)).toList();
    }
    return [];
  }

  Future<ChatMessage?> sendMessage({
    required String listingId,
    required String receiverId,
    required String message,
    String originalLang = 'en',
    String targetLang = 'si',
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/chat/send'),
      headers: _headers,
      body: jsonEncode({
        'listing_id': listingId,
        'receiver_id': receiverId,
        'message': message,
        'original_lang': originalLang,
        'target_lang': targetLang,
      }),
    );

    if (response.statusCode == 201) {
      final data = jsonDecode(response.body);
      return ChatMessage.fromJson(data['data']);
    }
    return null;
  }

  Future<int> getUnreadCount() async {
    final response = await http.get(
      Uri.parse('$baseUrl/api/chat-unread-count'),
      headers: _headers,
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['unread_count'] ?? 0;
    }
    return 0;
  }

  Future<void> markAsRead(String channel) async {
    await http.post(
      Uri.parse('$baseUrl/api/chat/$channel/read'),
      headers: _headers,
    );
  }
}
