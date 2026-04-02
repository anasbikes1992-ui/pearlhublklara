import 'dart:convert';
import 'package:http/http.dart' as http;

class LanguageService {
  final String baseUrl;

  static const Map<String, String> supportedLanguages = {
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

  LanguageService({required this.baseUrl});

  Future<String?> translate(String text, String from, String to) async {
    if (from == to) return text;

    try {
      final response = await http.post(
        Uri.parse('$baseUrl/api/translate'),
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'text': text, 'from': from, 'to': to}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['translated_text'];
      }
    } catch (_) {}
    return null;
  }
}
