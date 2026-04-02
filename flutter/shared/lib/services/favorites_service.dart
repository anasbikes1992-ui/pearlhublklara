import 'dart:convert';
import 'package:http/http.dart' as http;

class FavoritesService {
  final String baseUrl;
  final String token;

  FavoritesService({required this.baseUrl, required this.token});

  Map<String, String> get _headers => {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  Future<bool> toggleFavorite(String listingId, String listingType) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/favorites/toggle'),
      headers: _headers,
      body: jsonEncode({'listing_id': listingId, 'listing_type': listingType}),
    );

    if (response.statusCode == 200 || response.statusCode == 201) {
      final data = jsonDecode(response.body);
      return data['favorited'] ?? false;
    }
    return false;
  }

  Future<bool> checkFavorite(String listingId, String listingType) async {
    final response = await http.post(
      Uri.parse('$baseUrl/api/favorites/check'),
      headers: _headers,
      body: jsonEncode({'listing_id': listingId, 'listing_type': listingType}),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['favorited'] ?? false;
    }
    return false;
  }

  Future<List<Map<String, dynamic>>> getFavorites({String? listingType}) async {
    String url = '$baseUrl/api/favorites';
    if (listingType != null) url += '?listing_type=$listingType';

    final response = await http.get(Uri.parse(url), headers: _headers);

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return List<Map<String, dynamic>>.from(data['data'] ?? []);
    }
    return [];
  }
}
