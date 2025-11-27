import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DoctorService {

  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getDoctors(): Observable<any> {
    return this.http.get(`${this.apiUrl}/doctors.php`);
  }

  getDoctorDetails(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/doctors.php?id=${id}`);
  }
}
