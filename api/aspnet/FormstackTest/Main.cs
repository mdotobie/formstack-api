using System;
using Formstack;

namespace FormstackTest
{
	class MainClass
	{
		public static void Main (string[] args)
		{

			Formstack.Api fsApi = new Formstack.Api("1A8B2E148AC3331314A5658EA2E97E2AAA");
			Console.WriteLine(fsApi.Forms());
		}
	}
}
