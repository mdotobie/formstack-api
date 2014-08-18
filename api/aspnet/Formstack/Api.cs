using System;
using System.IO;
using System.Text;
using System.Net;
using System.Web;
using System.Collections.Generic;

namespace Formstack
{
	public class Api
	{
		public static string BaseUrl {
			
			get { return "https://www.formstack.com/api"; }
		}
		
		private string apiKey = null;
		
		public Api (string apiKey = "")
		{
			this.apiKey = apiKey;
		}
			
		public string Forms ()
		{
			
			return Formstack.Api.Request (this.apiKey, "forms", null);
		}
		
		public string Form (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "form", args);
		}
		
		public string Data (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "data", args);
		}
		
		public string Submission (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "data", args);
		}
		
		public string Submit (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "submit", args);
		}
		
		public string Edit (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "edit", args);
		}
		
		public string Delete (string id, IDictionary<string,string> args = null)
		{
			
			if (args == null)
				args = new Dictionary<string,string> ();	
			
			args.Add ("id", id);
			
			return Formstack.Api.Request (this.apiKey, "delete", args);
		}
		
		public static string Request (string apiKey, string method, IDictionary<string, string> args = null)
		{
            if (args == null)
                args = new Dictionary<string, string>();

            args.Add("api_key", apiKey);

			HttpWebRequest webRequest = (HttpWebRequest)WebRequest.Create (Formstack.Api.BaseUrl + "/" + method);
			webRequest.Method = "POST";
			webRequest.ContentType = "application/x-www-form-urlencoded";
			
			StringBuilder buff = new StringBuilder ();
			if (args != null) {
				foreach (KeyValuePair<string,string> kvp in args) {
					
					buff.Append (buff.Length == 0 ? "" : "&");
					buff.Append (WebUtility.HtmlEncode (kvp.Key));
					buff.Append ("=");
					buff.Append (WebUtility.HtmlEncode (kvp.Value));
				}
			}
			
			byte[] bytes = Encoding.UTF8.GetBytes (buff.ToString ());
			webRequest.ContentLength = bytes.Length;
			
			using (Stream requestStream = webRequest.GetRequestStream()) {
				requestStream.Write (bytes, 0, bytes.Length);
			}
			
			string response = "";

            try
            {
                using (WebResponse webResponse = webRequest.GetResponse())
                using (Stream responseStream = webResponse.GetResponseStream())
                using (StreamReader reader = new StreamReader(responseStream))
                {
                    response = reader.ReadToEnd();
                }
            }
            catch (WebException e)
            {
                using (Stream responseStream = e.Response.GetResponseStream())
                using (StreamReader reader = new StreamReader(responseStream))
                {
                    response = reader.ReadToEnd();
                }
            }

			return response;
			
		}
	}
}

